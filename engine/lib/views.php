<?php

/**
 * view - Renders a view (a normal PHP file that outputs content), captures the output content
 * and returns it as a string. An associative array of parameters is passed into the view
 * with the variable name $vars.
 *
 * View files are allowed to call methods defined in the engine/ directory to query the database
 * and access properties of models. They can also render other views.
 * 
 * Views are not allowed to use controllers or actions, perform access control, throw exceptions,
 * or end/forward the request, and are generally not allowed to modify state, 
 * but they are allowed to call PageContext methods to modify state for the current request.
 * 
 * view() adds a special variable $INCLUDE_COUNT, which increments each time a given view is rendered.
 *   $INCLUDE_COUNT is 0 the first time a view is rendered for a given script execution.
 *   This allows views to do one-time setup or generate unique DOM ids.
 *
 * @param string $view The name and location of the view to use
 * @param array $vars Any variables that the view requires, passed as an array
 * @return string The HTML content
 */
function view($view, $vars = null, $viewtype = null)
{
    // basic checking for bad paths
    if (strpos($view, '..') !== false)
    {
        return false;
    }

    if (empty($vars))
    {
        $vars = array();
    }

    if (!$viewtype)
    {
        $viewtype = Views::get_current_type();
    }
           
    ob_start();

    include_view($view, $viewtype, $vars);
    foreach (Views::get_extensions($view) as $extension_view)
    {
        include_view($extension_view, $viewtype, $vars);
    }    
    
    return ob_get_clean();
}

function get_view_path($view, $viewtype = null, $fallback = true)
{
    if (!$viewtype)
    {
        $viewtype = Views::get_current_type();
    }

    $viewPath = null;
    
    if ($viewtype != 'default')
    {
        $viewPath = Engine::get_real_path("views/{$viewtype}/{$view}.php");
        if (!$viewPath && !$fallback)
        {
            return null;
        }
    }
    
    if (!$viewPath)
    {
        $viewPath = Engine::get_real_path("views/default/{$view}.php");
    }
    
    return $viewPath;
}

function include_view($view, $viewtype, $vars)
{
    static $INCLUDE_COUNTS = array();

    $view_path = get_view_path($view, $viewtype);
        
    if (!isset($INCLUDE_COUNTS[$view_path]))
    {
        $INCLUDE_COUNTS[$view_path] = 0;
    }
    
    $INCLUDE_COUNT = $INCLUDE_COUNTS[$view_path];
    $INCLUDE_COUNTS[$view_path] = $INCLUDE_COUNT + 1;
    
    if (include_view_file($view_path, $vars, $INCLUDE_COUNT))
    {
        // success
    }
    else if (Config::get('debug'))
    {
        error_log(" [This view ({$view}) could not be included] ");
    }
}

function include_view_file($VIEW_PATH, $vars, $INCLUDE_COUNT)
{
    return include $VIEW_PATH;
}
/**
 * Returns whether the specified view exists
 *
 * @param string $view The view name
 * @param string $viewtype If set, forces the viewtype
 * @return true|false Depending on success
 */
function view_exists($view, $viewtype = null, $fallback = true)
{
    return get_view_path($view, $viewtype, $fallback) != null; 
}

/**
 * @param Entity $entity The entity to display

 * @return string HTML (etc) to display
 */
function view_entity($entity, $args = null) 
{
    if (!$args)
    {
        $args = array();
    }
    $args['entity'] = $entity;
    
    return $entity 
        ? view($entity->get_default_view_name(), $args)
        : '';
}

class Views
{
    private static $request_type = null;
    private static $current_type = null;
    private static $extensions_map = array();
    
    private static $browsable_types = array('mobile','default');
    
    static function extend($base_view, $extend_view, $priority = 1)
    {
        $extensions = @static::$extensions_map[$base_view];    
        if (!$extensions)
        {
            $extensions = array();
            static::$extensions_map[$base_view] =& $extensions;
        }        
        while (isset($extensions[$priority])) 
        {
            $priority++;
        }        
        
        $extensions[$priority] = $extend_view;
    }
    
    static function get_request_type()
    {
        $type = static::$request_type;
    
        if ($type === null)
        {
            $type = get_input('view') ?: @$_COOKIE['view'] ?: (is_mobile_browser() ? 'mobile' : 'default');
            
            if (preg_match('/[^\w]/', $type))
            {            
                $type = 'default';
            }
            static::$request_type = $type;
        }
        return $type;
    }
    
    static function set_request_type($type)
    {
        static::$request_type = $type;
    }
    
    static function get_current_type()
    {
        return static::$current_type ?: static::get_request_type();
    }
    
    static function set_current_type($type)
    {
        static::$current_type = $type;
    }
    
    static function is_browsable_type($type)
    {
        return in_array($type, static::$browsable_types);
    }
    
    static function get_extensions($base_view)
    {
        if (!isset(static::$extensions_map[$base_view]))
        {
            return array();
        }
        $extensions = static::$extensions_map[$base_view];
        if ($extensions)
        {
            ksort($extensions);
            return array_values($extensions);
        }
        else
        {
            return array();
        }
    }
}
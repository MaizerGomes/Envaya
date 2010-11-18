<?php

/**
 * Bypasses the engine to view simple cached views.
 */
 
require_once(dirname(__DIR__). '/engine/settings.php');

function load_engine()
{
    require_once(dirname(__DIR__) . "/engine/start.php");
}

function get_cache_filename($view, $viewtype)
{
    global $CONFIG;
    return $CONFIG->dataroot . 'views_simplecache/' . md5($viewtype . $view . $CONFIG->cache_version);
}

function output_cached_view($view, $viewtype)
{        
    global $CONFIG;

    $contents = '';
    
    if (empty($viewtype))
    {
        $viewtype = 'default';
    }

    $simplecache_enabled = $CONFIG->simplecache_enabled;

    if ($simplecache_enabled)
    {
        $filename = get_cache_filename($view, $viewtype);        
        if (file_exists($filename)) 
        {
            $contents = file_get_contents($filename);
            $cacheHeader = "1";
        }
        else
        {
            load_engine();
            $contents = view($view);
            if ($contents)
            {
                if (!file_exists($CONFIG->dataroot . 'views_simplecache'))
                {
                    @mkdir($CONFIG->dataroot . 'views_simplecache');
                }
                file_put_contents($filename, $contents);            
                $cacheHeader = "0";
            }
            else
            {
                header("HTTP/1.1 404 Not Found");                
                exit;
            }
        }        
        header("X-Cached: $cacheHeader\n");
    } 
    else 
    {
        load_engine();
        $contents = view($view);        
    }
            
    echo $contents;
}
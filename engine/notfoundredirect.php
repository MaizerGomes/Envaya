<?php

class NotFoundRedirect extends Model
{
    static $table_name = 'not_found_redirects';
    static $table_attributes = array(
        'pattern' => '',
        'replacement' => '',
        'order' => 1000,
    );
    
    function __toString()
    {
        return "id={$this->id} order={$this->order}: {$this->get_preg_pattern()} -> {$this->replacement}";
    }
    
    static function new_simple_redirect($old_path, $new_path)
    {
        $redirect = new NotFoundRedirect();
        $redirect->pattern = '^'.str_replace('/', '\/', $old_path).'\b';
        $redirect->replacement = $new_path;
        $redirect->validate();
        return $redirect;
    }

    function validate_error_handler($errno, $errmsg, $filename, $linenum, $vars)
    {            
        if (error_reporting() == 0) // @ sign
            return true; 
            
        throw new ValidationException($errmsg);
    }

    function validate()
    {
        set_error_handler(array('NotFoundRedirect','validate_error_handler'));    
        $this->try_get_redirect_url("/");
        restore_error_handler();    
    }    
    
    function try_get_redirect_url($url)
    {
        $redirect_url = preg_replace($this->get_preg_pattern(), $this->replacement, $url, 1, $count);
        if ($count > 0)
        {
            return $redirect_url;
        }    
        return null;
    }
    
    function get_preg_pattern()
    {
        return "/{$this->pattern}/i";
    }
    
    static function all()
    {
        return static::query()->order_by('`order`')->filter();
    }
    
    static function get_redirect_url($url)
    {        
        foreach (static::all() as $redirect)
        {
            $redirect_url = $redirect->try_get_redirect_url($url);
            if ($redirect_url)
            {
                return $redirect_url;
            }
        }
        return null;
    }
}
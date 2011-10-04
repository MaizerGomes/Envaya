<?php

/*
 * Interface for getting and setting session data.
 *
 * Main implementation is Session_Cookie, but this can be replaced e.g. to store session as SMS_State.
 */ 
class Session
{
    private static $loaded_user = false;
    private static $user;        
    private static $instance;
    
    static function set_instance($instance)
    {
        static::$instance = $instance;
    }
    
    static function get_instance()
    {
        return static::$instance;
    }

    static function get($key)
    {
        return static::$instance->get($key);
    }
    
    static function set($key, $value)
    {
        return static::$instance->set($key, $value);
    }    
    
    static function destroy()
    {
        return static::$instance->destroy();
    }

    static function start()
    {
        return static::$instance->start();
    }
    
    static function id()
    {
        return static::$instance->id();
    }
    
    static function save_input()
    {
        static::set('input', $_POST);
    }
    
    static function get_loggedin_user()
    {
        if (!static::$loaded_user)
        {
            static::$loaded_user = true;
            static::$user = static::$instance->get_loggedin_user();
        }
        return static::$user;
    }
    
    static function get_loggedin_userid()
    {
        $user = static::get_loggedin_user();
        return ($user) ? $user->guid : 0;       
    }

    static function isloggedin()
    {
        return static::get_loggedin_user() != null;
    }

    static function isadminloggedin()
    {    
        $user = static::get_loggedin_user();
        return ($user && $user->admin);
    }    
    
    /**
     * Logs in a specified User. 
     *
     * @param User $user A valid user object
     * @param boolean $persistent Should this be a persistent login?
     * @return true|false Whether login was successful
     */
    static function login($user, $persistent = false)
    {
        if ($user->check_rate_limit_exceeded())
            return false;

        static::$loaded_user = false;
        static::$instance->login($user, $persistent);

        EventRegister::trigger_event('login','user',$user);
        
        $user->reset_login_failure_count();
        $user->last_action = timestamp();
        $user->save();    

        return true;
    }    
    
    static function logout()
    {
        $curUser = Session::get_loggedin_user();
        if ($curUser)
        {
            EventRegister::trigger_event('logout','user',$curUser);
        }
        static::$loaded_user = false;        
        
        static::$instance->logout();
        return true;
    }
}

Session::set_instance(new Session_Cookie());
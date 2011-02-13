<?php

class Session
{
    private static $started = false;
    private static $dirty = false;
    private static $cookieName = 'envaya';

    static function get($key)
    {
        if (static::$started)
        {
            return @$_SESSION[$key];
        }
        else if (@$_COOKIE[static::$cookieName])
        {
            static::start();
            return @$_SESSION[$key];
        }
        else
        {
            return null;
        }
    }

    static function destroy()
    {
        session_destroy();
        setcookie(static::$cookieName, "", 0,"/");
    }

    static function id()
    {
        if (static::$started)
        {
            return session_id();
        }
        if (!static::$started && @$_COOKIE[static::$cookieName])
        {
            static::start();
            return session_id();
        }
        return null;

    }

    static function save_input()
    {
        static::set('input', $_POST);
    }

    static function start()
    {
        session_set_save_handler(
            array('Session', "_session_open"), 
            array('Session', "_session_close"), 
            array('Session', "_session_read"),
            array('Session', "_session_write"), 
            array('Session', "_session_destroy"), 
            array('Session', "_session_gc")
        );

        session_name(static::$cookieName);

        session_start();
        static::$started = true;

        register_event_handler('shutdown', 'system', 'session_write_close', 10);

        if (!isset($_SESSION['__elgg_session']))
        {
            static::set('__elgg_session', md5(microtime().rand()));
        }

        $guid = @$_SESSION['guid'];
        if ($guid)
        {
            $user = get_user($guid);
            if (!$user || $user->is_banned())
            {
                static::set($_SESSION['guid'], null);
            }
        }
    }

    static function is_dirty()
    {
        return static::$dirty;
    }

    static function set($key, $value)
    {
        if (!static::$started)
        {
            static::start();
        }
        if (is_null($value))
        {
            unset($_SESSION[$key]);
        }
        else
        {
            $_SESSION[$key] = $value;
        }
        static::$dirty = true;
    }

    static function cache_key($sessionId)
    {
        return make_cache_key("session", $sessionId);
    }    
    
    static function _session_open($save_path, $session_name)
    {
        return true;
    }    

    static function _session_close()
    {
        return true;
    }
           
    static function _session_read($id)
    {
        $cacheKey = static::cache_key($id);
        $sessionData = get_cache()->get($cacheKey);

        if ($sessionData == null)
        {
            $result = Database::get_row("SELECT * from users_sessions where session=?", array($id));
            $sessionData = ($result) ? $result->data : '';
            get_cache()->set($cacheKey, $sessionData);
        }

        return $sessionData;
    }
       
    static function _session_write($id, $sess_data)
    {
        if (Session::is_dirty())
        {
            get_cache()->set(static::cache_key($id), $sess_data);

            return (Database::update("REPLACE INTO users_sessions (session, ts, data) VALUES (?,?,?)",
                    array($id, time(), $sess_data))!==false);
        }
    }
    
    static function _session_destroy($id)
    {
        get_cache()->delete(static::cache_key($id));

        return (bool)Database::delete("DELETE from users_sessions where session=?", array($id));
    }
    
    static function _session_gc($maxlifetime)
    {
        $life = time()-$maxlifetime;
        
        return (bool)Database::delete("DELETE from users_sessions where ts<?", array($life));
    }
    
    static function get_loggedin_user()
    {
        return get_user(static::get('guid'));
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
}

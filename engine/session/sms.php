<?php

/*
 * Session implementation for SMS requests that wraps a SMS_State object
 */
class Session_SMS implements SessionImpl
{
    private $sms_state;

    function __construct($sms_state)
    {
        $this->sms_state = $sms_state;
    }    

    function get_logged_in_user()
    {
        return $this->sms_state->get_logged_in_user();
    }
    
    function login($user, $options)
    {            
        $this->sms_state->set_logged_in_user($user);       
        
        LogEntry::create('user:logged_in', $user);
    }
    
    function logout($user)
    {
        $this->sms_state->set_logged_in_user(null);
        
        if ($user)
        {
            LogEntry::create('user:logged_out', $user);
        }
    }
    
    function get($key)
    {
        return $this->sms_state->get($key);
    }        

    function set($key, $value)
    {
        $this->sms_state->set($key, $value);
    }
        
    function destroy()
    {
        $this->sms_state->delete();
    }

    function start()
    {
    }
    
    function id()
    {
        return $this->sms_state->id;
    }
    
    function is_high_security()
    {
        return false;
    }
    
    function is_medium_security()
    {
        return true;
    }
}
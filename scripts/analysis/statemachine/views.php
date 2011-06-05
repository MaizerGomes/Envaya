<?php
    /*
     * State machine that collects information about usage of views by observing calls to
     * the "view()" function with hardcoded view paths.
     */

    define('S_VIEWS_VIEW', 1);
    define('S_VIEWS_CALL', 2);
    define('S_VIEWS_PATH', 3);
    
    class StateMachine_Views extends StateMachine
    {
        public $views = array(/* key => list of files */);    
    
        private $cur_view_path;
    
        function get_next_state($token, $type, $line)
        {
            switch ($this->cur_state)
            {
                case S_INIT:    // look for calls to functions that take view path as 1st parameter
                    if ($type == T_STRING 
                        && ($token == 'view' || $token == 'include_view' || $token == 'view_exists'))
                    {
                        return S_VIEWS_VIEW;
                    }
                    return S_INIT;
                case S_VIEWS_VIEW: // previous token was a function name, expect (
                    if ($token == '(')
                    {
                        return S_VIEWS_CALL;
                    }
                    return $this->error($token, $type, $line);
                case S_VIEWS_CALL:        // in call to 'view(' function, look for a hardcoded view path
                    if ($type == T_CONSTANT_ENCAPSED_STRING)
                    {
                        $this->cur_view_path = eval("return $token;");
                        return S_VIEWS_PATH;
                    }
                    return S_INIT;                    
                case S_VIEWS_PATH: // ignore paths followed by a concatenation operator
                    if ($token == '.')
                    {
                        return S_INIT;
                    }
                    else
                    {
                        $this->views[$this->cur_view_path][] = "{$this->cur_path}:$line";
                        return S_INIT;
                    }                    
            }
        }
    }
    
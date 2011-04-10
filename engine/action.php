<?php

/* 
 * A class that represents one controller action, which can process input and render output.
 * Subclasses (defined in engine/action/) should override process_input() and render().
 * 
 * Can be used like so:
 *   $action = new Action_Subclass($controller)
 *   $action->esecute();
 *
 * Simple controller actions can just be defined as controller methods, without necessarily 
 * creating an Action subclass in a new file. But creating a Action subclass allows the logic
 * to be more easily reused elsewhere (and prevents controller files from getting very large).
 */
abstract class Action
{    
    protected $controller;
    
    function __construct($controller)
    {
        $this->controller = $controller;
        if (!$controller) { throw new Exception("controller is null"); }
    }
        
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->controller, $name), $arguments);
    }
    
    function execute()
    {
        $this->before();
    
        if (Request::is_post())
        {            
            $this->process_input();
        }
        else
        {
            $this->render();
        }    
        
        $this->after();
    }    

    /*
     * Subclasses should override to process the input from a POST request.
     * This function may forward the user to another page, or call render() on errors.
     */
    function process_input() 
    {
    }
    
    /*
     * Subclasses should override to render the page for a GET request (or POST request with errors)
     */    
    function render()
    {    
    }

    function before()
    {    
    }

    function after()
    {    
    }

    
    function render_captcha($vars)
    {
        $controller = $this->controller;
        $title = __('captcha:title');
        $controller->use_public_layout();
        $body = $controller->org_view_body($title, view("captcha", $vars));
        $controller->page_draw($title, $body); 
    }
    
    function check_captcha()
    {
        $user = Session::get_loggedin_user();
        
        $site_org = $this->get_org();
        
        // show captcha if not logged in, 
        // or if not approved and using someone else's site.
        $needsCaptcha = !$user || (!$user->is_approved() && $user->guid != $site_org->guid);
        
        if ($needsCaptcha)
        {        
            // after entering a correct captcha, we avoid prompting the user for a captcha again 
            // the next few times during the same session
            $free_captchas = Session::get('free_captchas');
            if ($free_captchas > 0)
            {
                Session::set('free_captchas', $free_captchas - 1);
                return true;
            }
        
			$valid_captcha = false;
			if (get_input('captcha'))
			{
				$res = Recaptcha::check_answer();
				if ($res->is_valid)
				{
                    Session::set('free_captchas', 3);
					$valid_captcha = true;
				}
				else
				{
					register_error(__('captcha:invalid'));
				}
			}
		
			if (!$valid_captcha)
			{
				return false;
			}
		}    
        
        return true;
    }    
}
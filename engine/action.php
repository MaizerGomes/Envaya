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
    static $view_types = array();
    
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
        $this->allow_view_types();
    
        if (Request::is_post())
        {           
            try
            {
                $this->validate_security_token();
                $this->process_input();
            }
            catch (ValidationException $ex)
            {
                $this->handle_validation_exception($ex);
            }
            
            $this->record_user_action();
        }
        else
        {
            $this->render();
        }    
        
        $this->after();
    }        
    
    protected function handle_validation_exception($ex)
    {
        if ($ex->is_html())
        {
            SessionMessages::add_error_html($ex->getMessage());
        }
        else
        {
            SessionMessages::add_error($ex->getMessage());
        }
        $this->render();
        if (!$this->get_request()->response)
        {
            Session::save_input();
            redirect_back();
        }    
    }
    
    protected function validate_security_token()
    {
        validate_security_token();    
    }
    
    protected function record_user_action()
    {
        $user = Session::get_loggedin_user();
        if ($user)
        {
            $user->last_action = time();
            $user->save();
        }                
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

    function allow_view_types()
    {
        $this->controller->allow_view_types(static::$view_types);
    }
    
    function render_captcha($vars)
    {
        $this->use_public_layout();        
        $this->page_draw(array(
            'title' => __('captcha:title'),
            'content' => view("captcha/captcha_form", $vars),
        ));
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
				$is_valid = Captcha::check_answer($_POST['captcha_response']);
				if ($is_valid)
				{
                    Session::set('free_captchas', 3);
					$valid_captcha = true;
				}
				else
				{
					SessionMessages::add_error(__('captcha:invalid'));
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
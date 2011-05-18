<?php

/*
 * The main controller that handles incoming web requests and forwards to child controllers.
 */
class Controller_Default extends Controller
{
    static $routes = array(
        array(
            'regex' => '/$', 
            'defaults' => array('controller' => 'home'), 
        ),
        array(
            'regex' => '/(?P<controller>pg|home|org|admin)\b',
        ),
        array(
            'regex' => '/(?P<username>[\w\-]{3,})\b', 
            'defaults' => array('controller' => 'usersite'), 
            'before' => 'init_user_by_username',
        ),
    );      
    
    public function execute($uri)
    {
        try
        {
            $request = $this->request;
            if ($request->init_exception)
            {
                throw $request->init_exception;
            }
        
            if (@$_GET['login'] && !Session::isloggedin())
            {
                $this->force_login();
            }

            if (@$_GET['lang'])
            {
                $this->change_viewer_language($_GET['lang']);
            }    
            
            $viewtype = @$_GET['view'];
            if ($viewtype && Views::is_browsable_type($viewtype))
            {
                set_cookie('view', $viewtype);
            }
            
            $viewtype = $viewtype ?: @$_COOKIE['view'] ?: ($this->request->is_mobile_browser() ? 'mobile' : 'default');            
            if (preg_match('/[^\w]/', $viewtype))
            {            
                $viewtype = 'default';
            }
            Views::set_request_type($viewtype);
            
            // work around flash uploader cookie bug, where the session cookie is sent as a POST field
            // instead of as a cookie
            if (@$_POST['session_id'])
            {
                $_COOKIE['envaya'] = $_POST['session_id'];
            }
            
            parent::execute($uri);
        }
        catch (NotFoundException $ex)
        {
            $this->not_found();
        }
        catch (RedirectException $ex)
        {
            $msg = $ex->getMessage();
            if ($msg)
            {
                SessionMessages::add_error($msg);
            }
            if ($this->request->is_post())
            {
                Session::save_input();
            }
            $this->redirect($ex->url, $ex->status);
        }
        catch (Exception $ex)
        {
            $this->server_error($ex);
        }
    }
    
    function init_user_by_username()
    {    
        $user = User::get_by_username($this->param('username'));
        
        $this->params['user_uri'] = $this->param('rest');
                
        if ($user)
        {
            $this->params['user'] = $user;

            if ($user instanceof Organization)
            {
                $this->params['org'] = $user;
            }
        }
        else
        {
            throw new NotFoundException();
        }
    }
}
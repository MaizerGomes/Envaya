<?php

class Action_Registration_LoggedIn extends Action
{    
    function before()
    {
        if (!Session::is_logged_in())
        {
            throw new RedirectException('', $this->get_redirect_url());
        }
    }

    function get_redirect_url()
    {
        return get_input('next') ?: "/pg/register";
    }
    
    function render()
    {        
        $this->allow_view_types(null);
        $this->page_draw(array(
            'title' => __("register:title"),
            'content' => view("account/register_logged_in", array('next' => get_input('next'))),
        ));
    }    

    function process_input()
    {            
        Session::logout();
        $this->redirect($this->get_redirect_url());            
    }
}

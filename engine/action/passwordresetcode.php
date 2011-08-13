<?php

class Action_PasswordResetCode extends Action
{
    private $user;

    function before()
    {
        $user_guid = get_input('u');        
        $user = User::get_by_guid($user_guid);
        if (!$user)
        {
            throw new NotFoundException();
        }    
        
        $this->user = $user;
    }
    
    function process_input()
    {
        $user = $this->user;
        $code = get_input('c');
        if ($user->has_password_reset_code($code))
        {
            $this->redirect("/pg/password_reset?u={$user->guid}&c={$code}");
        }
        else
        {
            throw new ValidationException(__('user:password:reset_code_incorrect'));
        }
    }
        
    function render()
    {    
        $this->prefer_https();
        $this->page_draw(array(
            'title' => __("user:password:reset_code"),
            'content' => view("account/password_reset_code", array('user' => $this->user)),
            'org_only' => true,
        ));                
    }
}    
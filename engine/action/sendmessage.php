<?php

class Action_SendMessage extends Action
{
    function before()
    {
        $this->require_login();
        $this->use_editor_layout();
        $this->require_org();
        
        $user = Session::get_loggedin_user();

        if (!$user->is_approved())
        {
            SessionMessages::add_error(__('message:needapproval'));
            redirect_back();
        }
    }
     
    function process_input()
    {
        $user = Session::get_loggedin_user();

        $recipient = $this->get_org();

        $subject = get_input('subject');
        if (!$subject)
        {
            throw new ValidationException(__("message:subject_missing"));
        }

        $message = get_input('message');
        if (!$message)
        {
            throw new ValidationException(__("message:empty"));
        }

        $mail = OutgoingMail::create($subject, $message);
        $mail->setFrom(Config::get('email_from'), $user->name);
        $mail->setReplyTo($user->email, $user->name);
        $mail->addBcc($user->email);
        
        if ($mail->send_to_user($recipient))
        {
            SessionMessages::add(__("message:sent"));
        }
        else
        {
            throw new ValidationException(__("message:not_sent"));
        }

        forward($recipient->get_url());
    }

    function render()
    {
        $org = $this->get_org();
        
        $user = Session::get_loggedin_user();

        PageContext::get_submenu('edit')->add_item(__("message:cancel"), $org->get_url());

        $this->page_draw(array(
            'title' => __("message:title"),
            'content' => view("org/compose_message", array('org' => $org, 'user' => $user)),
        ));        
    }
    
}    
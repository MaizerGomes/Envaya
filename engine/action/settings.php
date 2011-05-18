<?php

class Action_Settings extends Action
{
    function before()
    {
        $this->require_editor();
    }
     
    function process_input()
    {
        $user = $this->get_user();
        
        if (Session::isadminloggedin() && get_input('delete'))
        {
            $user->disable();
            $user->save();
            SessionMessages::add(__('user:deleted'));
            forward('/admin/user');
        }

        $name = get_input('name');

        if ($name)
        {
            if ($name != $user->name)
            {
                $user->name = $name;
                SessionMessages::add(__('user:name:success'));
            }
        }
        else
        {
            throw new ValidationException(__('register:no_name'));
        }

        $language = get_input('language');
        if ($language && $language != $user->language)
        {
            $user->language = $language;
            $this->change_viewer_language($user->language);
            SessionMessages::add(__('user:language:success'));
        }

        $email = trim(get_input('email'));
        if ($email != $user->email)
        {
            $user->email = validate_email_address($email);
            SessionMessages::add(__('user:email:success'));
        }

        $phone = get_input('phone');
        if ($phone != $user->phone_number)
        {
            $user->phone_number = $phone;
            SessionMessages::add(__('user:phone:success'));
        }

        if ($user instanceof Organization)
        {
            $notifications = get_bit_field_from_options(get_input_array('notifications'));
			
            if ($notifications != $user->notifications)
            {
                $user->notifications = $notifications;
                SessionMessages::add(__('user:notification:success'));
            }
        }

        $user->save();
        forward($user->get_url());
    }

    function render()
    {
        $this->page_draw(array(
            'title' => __('user:settings'),
            'content' => view("account/settings", array('entity' => $this->get_user())),
        ));                
    }    
}    
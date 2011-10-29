<?php

class Action_User_Settings extends Action
{  
    function process_input()
    {
        $user = $this->get_user();
        
        Permission_EditUserSettings::require_for_entity($user);
                                
        if (get_input('delete'))
        {
            Permission_UseAdminTools::require_for_entity($user);
        
            $user->disable();
            $user->save();
            SessionMessages::add(__('user:deleted'));
            return $this->redirect('/admin/entities');
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
            $user->set_email(EmailAddress::validate($email));
            SessionMessages::add(__('user:email:success'));
        }

        $phone = get_input('phone');
        if ($phone != $user->phone_number)
        {
            $user->set_phone_number($phone);
            SessionMessages::add(__('user:phone:success'));
        }

        $user->save();
        $this->redirect(get_input('from') ?: $user->get_url());
    }

    function render()
    {
        $user = $this->get_user();
    
        Permission_ViewUserSettings::require_for_entity($user);
    
        $this->use_editor_layout();
    
        $this->page_draw(array(
            'title' => __('user:settings'),
            'content' => view("account/settings", array('user' => $user)),
        ));                
    }    
}    
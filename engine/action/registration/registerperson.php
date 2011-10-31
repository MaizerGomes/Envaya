<?php

class Action_Registration_RegisterPerson extends Action
{
    function before()
    {
        $user = Session::get_logged_in_user();
        if ($user)
        {
            throw new RedirectException('', "/pg/register_logged_in");            
        }
    }

    function process_input()
    {
        $name = trim(get_input('name'));
        if (!$name)
        {
            throw new ValidationException(__('register:user:no_name'));
        }

        $username = trim(get_input('username'));

        User::validate_username($username, 6);

        $password = get_input('password');
        $password2 = get_input('password2');

        User::validate_password($password, $password2, $name, $username);

        $email = EmailAddress::validate(trim(get_input('email')));
        
        if (User::get_by_username($username, true))
        {
            throw new ValidationException(__('register:username_exists'));
        }

        if (!$this->check_captcha())
        {
            return $this->render_captcha();
        }
        
        $user = new Person();
        $user->username = $username;
        $user->set_phone_number(get_input('phone'));
        $user->set_email($email);
        $user->name = $name;
        $user->set_password($password);
        $user->language = Language::get_current_code();
        $user->setup_state = User::SetupComplete;
        $user->set_defaults();
        $user->save();
        
        $user->update_scope();
        $user->save();

        
        $user->init_default_widgets();        

        $mail = OutgoingMail::create(
            sprintf(__('register:notification_subject'), $user->name),
            secure_url($user->get_url())."\n"
        );
        $mail->send_to_admin();
                
        SessionMessages::add(__('register:created_ok'));                
        Session::login($user);
        $this->redirect_next($user);
    }    
    
    function redirect_next($user)
    {
        $next = get_input('next');
        throw new RedirectException('', $next ?: $user->get_url());
    }
    
    function render()
    {
        $this->page_draw(array(
            'title' => __('register:title'),
            'content' => view("account/register"),
        ));        
    }
}    
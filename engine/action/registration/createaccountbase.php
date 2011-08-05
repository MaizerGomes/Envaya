<?php

abstract class Action_Registration_CreateAccountBase extends Action
{
    protected $org = null;
    
    protected function get_login_url()
    {
        return '/pg/login';
    }
    
    protected function show_possible_duplicates($dups)
    {
        $this->page_draw(array(
            'title' => __("register:possible_duplicate"),
            'content' => view("org/possible_duplicate", array(
                'message' => __('register:possible_duplicate'), 
                'login_url' => $this->get_login_url(), 
                'duplicates' => $dups
            )),
            'org_only' => true,
        ));
    } 

    protected function post_process_input() {} // subclasses should override
    
    protected function get_country()
    {
        return null;
    }
    
    function process_input()
    {
        $name = trim(get_input('org_name'));

        if (!$name)
        {
            throw new ValidationException(__('register:no_name'));
        }

        $username = trim(get_input('username'));

        User::validate_username($username);

        $password = get_input('password');
        $password2 = get_input('password2');
        
        User::validate_password($password, $password2, $name, $username);

        $email = validate_email_address(trim(get_input('email')));

        if (!get_input('ignore_possible_duplicates'))
        {
            $dups = Organization::query()
                ->where("(username = ? OR (email = ? AND ? <> '') OR INSTR(name,?) > 0 OR INSTR(?,name) > 0)", 
                    $username, $email, $email, $name, $name)
                ->filter();  
                
            if (sizeof($dups) > 0)
            {
                return $this->show_possible_duplicates($dups);
            }
        }
        
        if (User::get_by_username($username, true))
        {
            throw new ValidationException(__('register:username_exists'));
        }                
        
        $org = new Organization();
        $org->set_defaults();
        
        $org->username = $username;
        $org->phone_number = get_input('phone');
        $org->email = $email;
        $org->name = $name;
        $org->set_password($password);        
        $org->language = Language::get_current_code();
        $org->setup_state = SetupState::CreatedAccount;
        
        $country = $this->get_country();
        if ($country)
        {        
            $org->country = $country;
            $org->set_design_setting('tagline', $org->get_country_text());     
            $org->geocode_lat_long();
        }
        
        $org->save();

        $org->init_default_widgets();
        
        login($org, false);

        SessionMessages::add(__('register:created_ok'));   

        $this->org = $org;
        
        $this->post_process_input();
    }      
}
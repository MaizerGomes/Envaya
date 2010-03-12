<?php
    
    action_gatekeeper();
        
    try 
    {
        $name = trim(get_input('org_name'));
        
        if (!$name)
        {
            throw new RegistrationException(elgg_echo('create:no_name'));
        }
    
        $username = trim(get_input('username'));
        
        if (!validate_username($username)) 
        {
            throw new RegistrationException(elgg_echo('create:bad_username'));
        }            
    
        access_show_hidden_entities(true);
        
        if (get_user_by_username($username)) 
        {
            throw new RegistrationException(elgg_echo('create:username_exists'));
        }
    
    
        $password = get_input('password');
        $password2 = get_input('password2');
    
        if (strcmp($password, $password2) != 0)
        {
            throw new RegistrationException(elgg_echo('create:passwords_differ'));
        }
        
        if (!validate_password($password)) 
        {    
            throw new RegistrationException(elgg_echo('create:bad_password'));
        }                   
        
        $email = trim(get_input('email'));
        
        if (!validate_email_address($email)) 
        {
            throw new RegistrationException(elgg_echo('create:bad_email'));
        }    
                    
        $org = new Organization();
        $org->username = $username;
        $org->email = $email;
        $org->name = $name;
        $org->access_id = ACCESS_PUBLIC;
        $org->salt = generate_random_cleartext_password(); 
        $org->password = generate_user_password($org, $password); 
        $org->owner_guid = 0; 
        $org->container_guid = 0;                 
        $org->language = get_language();
        $org->setup_state = 3; 
        
        $prevInfo = $_SESSION['registration'];

        //$org->registration_number = $prevInfo['registration_number'];
        $org->country = $prevInfo['country'];
        $org->local = $prevInfo['local'];        
        
        $org->save();        

        $guid = $org->guid;

        login($org, false);

        system_message(sprintf(elgg_echo("create:ok"),$CONFIG->sitename));

        forward("org/new?step=3");
    } 
    catch (RegistrationException $r) 
    {    
        $_SESSION['input'] = $_POST;
        
        register_error($r->getMessage());
        forward_to_referrer();
    }
    
?>    
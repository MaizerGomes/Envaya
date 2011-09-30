<?php

return array(
    'register:step1' => '1. Qualification',
    'register:step2' => '2. Create account',
    'register:step3' => '3. Set up homepage',
    'register:title' => 'Register for Envaya',

    'register:notemail' => "The email address '%s' does not appear to be a valid email address.",
    'register:userexists' => 'Somebody has already registered that username.',
    'register:usernametooshort' => 'Your username must be a minimum of {min} characters long.',
    'register:passwordtooshort' => 'The password must be a minimum of {min} characters long.',
    'register:invalidchars' => 'Sorry, the username "%s" is invalid because it contains the character "%s". Please choose a username using only the following characters: a-z 0-9 - _',
    'register:username_letter' => "Sorry, the username \"%s\" is invalid. Please choose a username that begins with a letter (a-z).",
    'register:usernamenotvalid' => 'Sorry, the username "%s" is invalid. Please choose another.',
    
    'register:already_logged_in' => "You are currently logged in as {name}.",
    'register:must_log_out' => "In order to register a new account, you must log out.",

    // qualification
    'register:welcome' => "Welcome to Envaya! In just a few minutes, your organization will have a website of its own, for free, where you can share news about your projects and let everyone know about your work.",
    'register:qualify_instructions' => "First, we need to check whether your organization qualifies to use Envaya. Envaya will verify these qualifications before making your website accessible to the public.",
    'register:org_type' => 'What type of organization are you?',
    'register:org_type:non_profit' => 'Non-profit civil society organization',
    'register:org_type:for_profit' => 'Business',
    'register:org_type:other' => 'Other',
    'register:country' => 'What country does your organization operate in?',
    'register:click_to_continue' => 'Click the button below to continue.',
    'register:next_step' => 'Next step',
    'register:wrong_country' => "Sorry, currently only organizations in Tanzania can register for Envaya. We hope to support other countries soon.",
    'register:wrong_org_type' => "Sorry, Envaya is only intended for non-profit civil society organizations at this time.",
    'register:qualify_missing' => "Your qualification information was not found. Please complete the qualification form again.",
    'register:qualify_ok' => "Congratulations! Your organization appears to qualify for a website on Envaya.",    

    // create account    
    'register:account_instructions' => "Now, enter a few pieces of information to create your account on Envaya. This account will let you log in and update your organization's website.",
    'register:org_name' => "Enter the full name of your organization:",
    'register:org_name:help' => "This will be the title of your website.",
    'register:username' => "Choose your organization's username for Envaya:",
    'register:username2' => "(A short name for your organization)",
    'register:username:help' => "Your web address will be:",
    'register:username:help2' => "Your username must be at least {min} characters, and can contain letters (a-z), digits (0-9), dashes (-), and underscores (_).",
    'register:username:placeholder' => 'username',
    'register:password' => "Choose a password for your account:",
    'register:password:help' => "Together with your username, this will let you log in to edit your website.",
    'register:password:remember' => "Remember this password and keep it secure.",
    'register:password:length' => "Your password must be at least {min} characters long.",
    'register:password2' => "Enter the password again to confirm:",
    'register:email' => "Enter your organization's email address:",
    'register:email:help' => "If your organization doesn't have an email address, use the email address of someone in your organization.",
    'register:email:help_2' => "If no one in your organization has an email address, leave this blank.",

    'register:phone' => "Enter your organization's phone number:",
    'register:phone:help' => "Enter your mobile phone number if you have one.",
    'register:phone:help_2' => "If you have multiple phone numbers, separate them with a comma.",

    'register:click_to_create' => 'Click the button below to create your account.',
    'register:create_button' => 'Create account',
    'register:no_name' => "Please enter your organization's name.",
    'register:username_exists' => 'Somebody else has already registered that username. Please choose another.',
    'register:passwords_differ' => 'The two passwords you entered did not match. Please try entering them again.',
    'register:password_too_easy' => 'Your password is too easy to guess. Please choose a different password.',
    'register:created_ok' => 'Congratulations! Your account was created successfully.',
    
    'register:possible_duplicate' => "Organization already registered?",
    'register:duplicate_instructions' => "If your organization is shown below, click on it to log in using your organization's existing username and password.",
    'register:not_duplicate_instructions' => "If your organization is not shown above, click the button below to continue and create a new account for your organization.",
    'register:not_duplicate' => "Continue registration",    
        
    // profile setup
    'register:homepage_instructions' => "Now we will ask a few questions in order to create a homepage for your organization.",
    'register:mission:blank' => "Please enter the mission of your organization.",
    'register:mission:help' => "This will appear at the top of your homepage.",
    'register:location' => "Where is your organization located?",
    'register:city' => 'City:',
    'register:region' => 'Region:',
    'register:region:blank' => 'Select your region',
    'register:sector' => "Choose at most five sectors for your organization:",
    'register:sector:blank' => "Please select at least one sector that applies for your organization.",
    'register:sector:toomany' => "Too many sectors selected for your organization. Please select at most 5 sectors.",
    'register:sector:other_specify' => "If 'Other', specify:",
    'register:theme' => "Choose a theme for your website:",
    'register:theme:help' => "Later you can customize the design of your website by adding your organization's logo at the top of each page.",
    'register:homepage_label' => "Click the button below to view your homepage!",
    'register:homepage_button' => "Let's see it!",
    'register:homepage_created' => "Your homepage was created successfully.",           
    
    // approval email
    'register:approval_email:subject' => 'Your website has been approved',
    'register:approval_email:congratulations' => "Congratulations! Your website has been approved by Envaya and it's now online:",
    'register:approval_email:nextsteps' => "Now, continue building your website by choosing a design, adding news and photos, and adding other pages about your organization's history, projects, team, and other topics.",
    'register:approval_email:login' => "To do this, log in to edit your website here:",
    'register:approval_email:share' => "Also, be sure to share your new website with your partner organizations, community members, and other stakeholders, by sending them a link to %s .",
    'register:approval_email:help' => "For instructions on how to get started using Envaya, and to learn what you can do with your new website, visit our %s page here:",
    'register:approval_email:thanks' => "Thanks for using Envaya!",    
    
    
    // individual user registration    
    'register:user:instructions' => "This page allows you to register a personal user account on Envaya.",
    'register:user:name' => "Your full name:",
    'register:user:no_name' => "Please enter your name.",
    'register:user:password:help' => "Together with your username, this will let you log in to Envaya.",
    'register:user:email' => "Enter your email address:",
    'register:user:email:help' => "If you don't have an email address, leave this blank.",
    'register:user:email:help_2' => "Your email address will not be publicly displayed, given away, or added to a mailing list.",
    'register:user:phone' => "Enter your phone number:",    
    'register:user:username' => "Choose a username for your account:",
    'register:if_org' => "To register an organization, %s.",
    'register:already_registered' => "You are already registered.",    
    
     
);
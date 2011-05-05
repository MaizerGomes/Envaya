<?php

/*
 * Installs test data required for selenium tests. Recommended for development computers,
 * not to be used on production servers.
 */

require_once("engine/start.php");

function main()
{   
    install_admin();
    
    $admin_email = Config::get('admin_email');
    
    for ($i = 0; $i < 22; $i++)
    {
        $org = install_org("testposter$i");        
        $org->phone_number = "cell: $i$i$i$i$i$i$i, fax: +124124129481";                        
        $org->name = "Test Poster$i";
        $org->email = str_replace('@',"+p$i@", $admin_email);
        $org->save();        
    }
    
    install_org('testorg');
    install_grantmaker();
    install_envaya();
}

function install_admin()
{
    $admin = User::get_by_username('testadmin');
    if (!$admin)
    {
        $admin = new User();
        $admin->username = "testadmin";
        $admin->set_password('testtest');
        $admin->name = "Test Admin";
        $admin->email = Config::get('admin_email');
        $admin->admin = true;    
        $admin->save();
    }
}

function install_org($username)
{
    $org = Organization::query()->where('username = ?', $username)->get();
    if (!$org)
    {    
        $org = new Organization();
        $org->username = $username;
    }

    $org->email = Config::get('admin_email');
    $org->set_design_setting('theme_name', 'green');
    $org->set_design_setting('tagline', 'a test organization');
    $org->set_design_setting('share_links', array('email', 'facebook', 'twitter'));
    $org->name = "Test Org";
    $org->set_password('testtest');
    $org->language = 'en';
    $org->set_sectors(array(6,19));
    $org->country = 'tz';
    $org->setup_state = SetupState::CreatedHomePage;
    $org->set_lat_long(-6.140555,35.551758);
    $org->approval = 1;
    $org->save();
    
    $home = $org->get_widget_by_class('Home');
    $home->save();
            
    $home->get_widget_by_class('Mission')->save();        
    $home->get_widget_by_class('Updates')->save();        
    $home->get_widget_by_class('Sectors')->save();
    $home->get_widget_by_class('Location')->save();    
    
    $org->get_widget_by_class('News')->save();
    $org->get_widget_by_class('Contact')->save();
    
    return $org;
}
    
function install_grantmaker()
{
    $org = install_org('testgrantmaker');
    
    $org->name = "Test Grantmaker";
    $org->save();
    
    $reports = $org->get_widget_by_name('reports');
    $reports->menu_order = 80;
    $reports->subclass = 'ReportDefinitions';
    $reports->save();
}
    
function install_envaya()    
{
    $envaya = Organization::query()->where('username = ?', 'envaya')->get();
    if (!$envaya)
    {
        $envaya = new Organization();
        $envaya->username = 'envaya';
        $envaya->email = Config::get('admin_email');
        $envaya->name = 'Envaya';
        $envaya->set_password('testtest');
        $envaya->language = 'en';
        $envaya->set_design_setting('theme_name', 'sidebar');
        $envaya->country = 'us';
        $envaya->set_lat_long(37,-112);
        $envaya->setup_state = SetupState::CreatedHomePage;
        $envaya->approval = 1;
        $envaya->save();
    }
    
    $home = $envaya->get_widget_by_name('home');
    $home->subclass = 'Hardcoded';
    $home->handler_arg = 'page/about';
    $home->title = 'About Us';
    $home->save();
      
    $envaya->get_widget_by_name('news')->save();

    $contact = $envaya->get_widget_by_name('contact');
    $contact->subclass = 'Hardcoded';
    $contact->handler_arg = 'page/contact';
    $contact->save();

    $donate = $envaya->get_widget_by_name('contribute');
    $donate->subclass = 'Hardcoded';
    $donate->handler_arg = 'page/donate';
    $donate->title = 'Contribute';
    $donate->in_menu = 1;
    $donate->save();
}

main();
 
Config::set('debug', false);

print "done";
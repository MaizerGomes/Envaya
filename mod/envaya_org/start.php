<?php

Engine::add_autoload_action('ClassRegistry', function() {
    ClassRegistry::register(array(
        'core.featured.site' => 'FeaturedSite',
        'core.featured.photo' => 'FeaturedPhoto',
        'core.permission.editmainsite' => 'Permission_EditMainSite',
    ));
});

Engine::add_autoload_action('Controller_Default', function() {
    Controller_Default::add_route(array(
        'regex' => '/org\b',
        'controller' => 'Controller_Org',
    ), 0);

    Controller_Default::add_route(array(
        'regex' => '/($|home\b)',
        'controller' => 'Controller_EnvayaHome',
    ), 0);
});

Engine::add_autoload_action('Controller_Admin', function() {
    Controller_Admin::add_route(array(
        'regex' => '/envaya\b',
        'controller' => 'Controller_EnvayaAdmin',
    ));
});

Engine::add_autoload_action('Language', function() {
    Language::add_fallback_group('featured', 'featured_admin');
});

Engine::add_autoload_action('Hook_ViewWidget', function() {
    Hook_ViewWidget::register_handler('Handler_EnvayaViewWidget');
});

Engine::add_autoload_action('Hook_ViewDashboard', function() {
    Hook_ViewDashboard::register_handler('Handler_EnvayaViewDashboard');
});

Views::extend('account/login_links', 'account/envaya_login_links');    
Views::extend('account/register_content', 'account/envaya_register_content', -1);    
Views::extend('page_elements/html_start', 'page_elements/envaya_topbar');
Views::extend('page_elements/head_content', 'page_elements/envaya_favicon');
Views::extend('css/default', 'css/snippets/topbar');
Views::extend('css/editor', 'css/snippets/slideshow');
Views::extend('messages/usersite', 'messages/envaya_usersite', -1);
Views::extend('messages/dashboard', 'messages/envaya_dashboard', -1);      
Views::replace('emails/network_relationship_invite_link', 'emails/envaya_relationship_invite');
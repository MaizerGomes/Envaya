<?php

Engine::add_autoload_action('EntityRegistry', function() {
    EntityRegistry::register_subtype('core.featured.site', 'FeaturedSite');
    EntityRegistry::register_subtype('core.featured.photo', 'FeaturedPhoto');
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
    ), 0);
});

Engine::add_autoload_action('Language', function() {
    Language::add_fallback_group('featured', 'featured_admin');
});

Views::extend('admin/dashboard_items', 'admin/envaya_dashboard_items');    
Views::extend('account/login_links', 'account/envaya_login_links');    
Views::extend('admin/org_actions_items', 'admin/envaya_org_actions_items');    
Views::extend('page_elements/html_start', 'page_elements/envaya_topbar');
Views::extend('page_elements/head_content', 'page_elements/envaya_favicon');
Views::extend('css/default', 'css/snippets/topbar');
Views::extend('css/editor', 'css/snippets/slideshow');
Views::replace('emails/network_relationship_invite_link', 'emails/envaya_relationship_invite');
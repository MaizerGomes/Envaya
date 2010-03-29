<?php
    gatekeeper();
    set_context('editor');
    
    $org_guid = get_input('org_guid');
    $org = get_entity($org_guid);
        
    set_context('editor');
    
    if ($org) 
    {             
        add_submenu_item(elgg_echo("message:cancel"), $org->getURL(), 'b');                
    
        $title = elgg_echo("message:title");
        $area1 = elgg_view("org/composeMessage", array('entity' => $org));
        $body = elgg_view_layout("one_column", elgg_view_title($title), $area1);                
        page_draw($title,$body);
    }
    else 
    {
        not_found();
    }
        
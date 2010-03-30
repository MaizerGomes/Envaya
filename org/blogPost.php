<?php

    $postId = (int) get_input('blogpost');

    if ($post = get_entity($postId)) 
    {        
        $canedit = $post->canEdit();
        if ($canedit) 
        {
            add_submenu_item(elgg_echo("widget:edit"), "{$post->getUrl()}/edit", 'edit');                    
        }
    
        $org = $post->getContainerEntity();
        set_theme($org->theme);
        
        $title = elgg_echo('org:news');
        
        if (!$org->canView())
        {            
            $org->showCantViewMessage();
            $body = '';
        }
        else
        {        
            $area2 = elgg_view("org/blogPost", array('entity'=> $post));        
            $body = elgg_view_layout("one_column", org_title($org, $title), $area2);            
        }    
        
        page_draw($title,$body);
    } 
    else 
    {
        not_found();        
    }
        
?>
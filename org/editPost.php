<?php
    gatekeeper();
        
    $postid = (int) get_input('blogpost');
    $post = get_entity($postid);
    $title = elgg_echo('blog:editpost');
    
    set_context('editor');
    
    if ($post && $post->canEdit()) 
    {                   
        add_submenu_item(elgg_echo("blog:canceledit"), $post->getUrl(), 'b');                
    
        $org = $post->getContainerEntity();
        $area1 = elgg_view("org/editPost", array('entity' => $post));
        $body = elgg_view_layout("one_column", elgg_view_title($title), $area1);        
    }
    else 
    {
        $body = elgg_view('org/contentwrapper',array('body' => elgg_echo('org:noaccess')));
    }
    
    page_draw($title,$body);      

?>
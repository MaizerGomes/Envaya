<?php

    gatekeeper();
    action_gatekeeper();

    $guid = (int) get_input('blogpost');
    $body = get_input('blogbody');
    $blog = get_entity($guid);    
    
    if ($blog->getSubtype() != T_blog || !$blog->canEdit()) 
    {
        register_error(elgg_echo("org:cantedit"));
        forward_to_referrer();
    }
    else if (empty($body)) 
    {
        register_error(elgg_echo("org:cantedit"));
        forward_to_referrer();
    }    
    else 
    {
        $blog->access_id = ACCESS_PUBLIC;
        $blog->content = $body;

        if (!$blog->save()) 
        {
            register_error(elgg_echo("blog:error"));
            forward();
        }
        
        if (isset($_FILES['image']) && $_FILES['image']['size'])
        {   
            if (substr_count($_FILES['image']['type'],'image/'))
            {
                $blog->setImage(get_uploaded_file('image'));        
            }   
            else
            {
                register_error(elgg_echo('upload:invalid_image'));
            }
        }        
        else if (get_input('deleteimage'))
        {
            $blog->setImage(null);
        }        

        system_message(elgg_echo("blog:posted"));
        forward($blog->getUrl());                    
    }
   
?>

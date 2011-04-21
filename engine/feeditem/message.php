<?php

class FeedItem_Message extends FeedItem
{
    function is_valid()
    {
        $message = $this->get_subject_entity();
        if (!$message || !$message->is_enabled())
            return false;
        
        $container = $message->get_container_entity();
        if (!$container || !$container->is_enabled())
            return false;
        
        return true;
    }

    function render_heading($mode)
    {
        $message = $this->get_subject_entity();        
        $topic = $message->get_container_entity();
        
        $is_first_message = $message->guid == $topic->first_message_guid;
        
        return sprintf($is_first_message ? __('discussions:feed_heading_topic') : __('discussions:feed_heading_message'), 
            $this->get_org_link($mode),
            "<a href='{$topic->get_url()}'>".escape($topic->subject)."</a>"
        );                    
    }
    
    function render_content($mode)
    {
        $message = $this->get_subject_entity();       
        $topic = $message->get_container_entity();
        
        return view('feed/snippet', array(            
            'link_url' => $this->get_url(),
            'content' => escape($message->from_name) . ": " . $message->render_content(Markup::Feed)
        ));
    }    
}
<?php

class FeedItem_Relationship extends FeedItem
{
    function render_heading($mode)
    {
        $relationship = $this->get_subject_entity();
        
        $url = escape($relationship->get_subject_url());
        if ($url)
        {
            $subject_html = "<a class='feed_org_name' href='$url'>".escape($relationship->get_subject_name())."</a>";
        }
        else
        {
            $subject_html = escape($relationship->get_subject_name());
        }
        
        $org = $relationship->get_container_entity();
        $network = $org->get_widget_by_class('Network');
        
        $list_name = $relationship->__('header');
        
        if ($network->is_active())
        {
            $list_name = "<a href='{$network->get_url()}'>$list_name</a>";
        }        
        
        return sprintf(__('network:feed_heading'), 
            $this->get_org_link($mode),
            $subject_html,
            $list_name
        );
    }
    
    function render_content($mode)
    {
        $relationship = $this->get_subject_entity();
        
        if ($relationship->content)
        {
            return view('feed/snippet', array(            
                'link_url' => $this->get_url(),
                'content' => $relationship->render_content(Markup::Feed)
            ));
        }
        return '';
    }    
}
<?php

class FeedItemHandler_Relationship extends FeedItemHandler
{
    function render_heading($item, $mode)
    {
        $relationship = $item->get_subject_entity();
        
        $url = escape($relationship->get_subject_url());
        if ($url)
        {
            $subject_html = "<a class='feed_org_name' href='$url'>".escape($relationship->get_subject_name())."</a>";
        }
        else
        {
            $subject_html = escape($relationship->get_subject_name());
        }
        
        return sprintf($relationship->get_feed_heading_format(), 
            $this->get_org_link($item, $mode),
            $subject_html
        );
    }
    
    function render_content($item, $mode)
    {
        $update = $item->get_subject_entity();

        return view('feed/snippet', array(            
            'link_url' => $this->get_url($item),
            'content' => $update->render_content(Markup::Feed)
        ));
    }    
}
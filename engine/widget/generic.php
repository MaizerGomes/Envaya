<?php

/* 
 * A widget with free-text HTML content, and possibly a custom title.
 * Drafts of the content can be saved before publishing.
 */
class Widget_Generic extends Widget
{
    function render_view($args = null)
    {
        return view("widgets/generic_view", array('widget' => $this));
    }

    function render_edit()
    {
        return view("widgets/generic_edit", array('widget' => $this));
    }

    function process_input($action)
    {
        $publish = ($this->publish_status == Widget::Published);        
        $time = time();
        $lastPublished = (int)$this->get_metadata('last_publish_time');

        $title = get_input('title');
        if ($title)
        {
            $this->title = $title;
        }
        
        if (!$this->get_title())
        {
            throw new ValidationException($this->is_section() ? __('widget:no_section_title') : __('widget:no_title'));
        }       
                
        $content = get_input('content');
        if ($publish)
        {
            $this->set_metadata('last_publish_time', $time);
        }
                
        $this->set_content($content);
        $this->save();         
        
        $revision = ContentRevision::get_recent_draft($this);
        $revision->time_updated = $time;
        $revision->publish_status = $this->publish_status;
        $revision->content = $content;            
        $revision->save();                
            
        if ($publish && $this->content)
        {
            if (!$lastPublished)
            {
                $this->post_feed_items();
            }
            else if (!Session::isadminloggedin() && $time - $lastPublished > 86400)
            {
                $this->post_feed_items_edit();
            }
        }
    }
}
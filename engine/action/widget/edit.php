<?php

class Action_Widget_Edit extends Action
{
    function before()
    {
        parent::before();
        
        if (Input::get_string('_draft'))
        {
            $this->set_content_type('text/javascript');
        }
        
        Permission_EditUserSite::require_for_entity($this->get_widget());
    }
    
    protected function save_draft()
    {
        $widget = $this->get_widget();
        $widget->save_draft(Input::get_string('content'));                       
        
        $this->set_content(json_encode(array('guid' => $widget->guid)));    
    }
    
    function process_input()
    {        
        if (Input::get_string('_draft'))
        {
            return $this->save_draft();
        }

        $widget = $this->get_widget();        
    
        if (Input::get_string('delete'))
        {   
            $widget->publish_status = Widget::Deleted;
            $widget->save();

            SessionMessages::add(!$widget->is_section() ? __('widget:delete:success') : __('widget:delete_section:success'));            

            $this->redirect($widget->get_container_entity()->get_edit_url());
        }
        else
        {            
            $widget->publish_status = Widget::Published;
            $widget->time_updated = timestamp();
            
            $container = $widget->get_container_entity();
            if (($container instanceof Widget) && $container->publish_status != Widget::Published)
            {
                $container->publish_status = Widget::Published;
                $container->save();
            }

            $widget->process_input($this);             
            
            $response = $this->get_response();            
            if ($response->status == 200 && !$response->content)
            {            
                SessionMessages::add(__('widget:save:success'));            
                $this->redirect($widget->get_url());
            }
        }
    }
    
    function render()
    {
        $widget = $this->get_widget();        
        
        $this->use_editor_layout();
        
        
        $cancelUrl = Input::get_string('from') ?: $widget->get_url();

        PageContext::get_submenu('top')->add_link(__("canceledit"), $cancelUrl);

        $this->page_draw(array(
            'title' => sprintf(__('edit_item'), $widget->get_title()),
            'header' => view('widgets/edit_header', array('widget' => $widget)),
            'content' => $widget->render_edit()
        ));
    }
}
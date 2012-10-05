<?php

abstract class Action_ContactTemplate_Edit extends Action
{       
    function before()
    {
        Permission_SendMessage::require_for_root();
    }
 
    abstract function update_template($template);
 
    protected function save_draft()
    {
        $this->set_content_type('text/javascript');
        
        Input::validate_security_token();
            
        $template = $this->get_template();       
            
        $content = Input::get_string('content');                       
        
        $template->save_draft($content);
        
        $template->set_content($template);
        $template->save();
        
        $this->set_content(json_encode(array('guid' => $template->guid)));    
    }
 
    function process_input()
    {
        $template = $this->get_template();
        
        if (Input::get_string('_draft'))
        {
            $this->save_draft();        
        }        
        else if (Input::get_string('delete'))
        {
            $template->delete();
            $this->redirect($this->controller->index_url);
        }
        else
        {
            $template->filters_json = Input::get_string('filters_json');            
            $this->update_template($template);
            
            $template->save();
            $this->redirect($template->get_url());       
        }
    }

    function render()
    {
        $template = $this->get_template();
    
        PageContext::get_submenu('top')->add_link(
            __('canceledit'), 
            Input::get_string('from') ?: $template->get_url());
    
        $this->page_draw(array(
            'title' => sprintf(__('contact:edit_template'), $this->get_type_name()),
            'header' => $this->get_header(array(
                'template' => $template,
                'title' => __('edit')
            )),
            'content' => view($this->controller->edit_view, array('template' => $template)),
        ));
    }    
}    
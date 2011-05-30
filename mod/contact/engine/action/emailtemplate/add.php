<?php

class Action_EmailTemplate_Add extends Action
{
    function before()
    {
        $this->require_admin();
    }
     
    function process_input()
    {
        $content = get_input('content');
        
        $email = new EmailTemplate();
        $email->from = get_input('from');
        $email->subject = get_input('subject');        
        $email->set_content($content);
        $email->save();
        $this->redirect($email->get_url());
    }

    function render()
    {
        $this->page_draw(array(
            'title' => __('email:add'),
            'header' => view('admin/email_header', array(
                'email' => null,
                'title' => __('add')
            )),                        
            'content' => view('admin/add_email'),
        ));     
    }    
}    
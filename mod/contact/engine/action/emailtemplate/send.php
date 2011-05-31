<?php

class Action_EmailTemplate_Send extends Action
{
    function before()
    {
        $this->require_admin();
    }
     
    function process_input()
    {
        $email = $this->get_email();
                
        $org_guids = get_input_array('users');
        $numSent = 0;
        foreach ($org_guids as $org_guid)
        {       
            $org = Organization::get_by_guid($org_guid);

            if ($email->can_send_to($org))
            {
                $numSent++;
                $email->send_to($org);
            }
        }
        $email->update();
        
        SessionMessages::add("sent $numSent emails");
        $this->redirect(get_input('from') ?: "{$email->get_url()}/send");
    }

    function render()
    {
        $email = $this->get_email();
        
        $org_guids = get_input_array('orgs');
        if ($org_guids)
        {
            $orgs = Organization::query()->where_in('guid', $org_guids)->filter();
        }
        else
        {         
            $orgs = Organization::query()
                ->where('approval > 0')
                ->where("email <> ''")
                ->where('(notifications & ?) > 0', Notification::Batch)
                ->where("not exists (select * from outgoing_mail where email_guid = ? and to_guid = users.guid)", $email->guid)
                ->order_by('guid')
                ->limit(50)
                ->filter(); 
        }

        PageContext::get_submenu('edit')->add_item(__('cancel'), get_input('from') ?: $email->get_url());
        
        $this->page_draw(array(
            'title' => __('contact:send_email'),
            'header' => view('admin/email_header', array(
                'email' => $email,
                'title' => __('email:send')
            )),            
            'content' => view('admin/batch_email', array('email' => $email, 'users' => $orgs)),
        ));        
    }
}    
<?php

class Action_User_Share extends Action
{
    function before()
    {
        $this->require_login();

        $user = Session::get_loggedin_user();
        $recentSharedEmails = SharedEmail::query()
            ->where('user_guid = ?', $user->guid)
            ->where('time_shared > ?', timestamp() - 86400)
            ->count();                        
            
        if ($recentSharedEmails > 60)
        {
            throw new RedirectException(__('share:rate_limit_exceeded'), "/pg/blank");
        }        
    }
    
    function process_input()
    {        
        $user = Session::get_loggedin_user();        
        
        $url = get_input('u');
    
        $message = get_input('message');
        if (!$message)
        {
            throw new ValidationException(__('message:empty'));
        }
        
        $subject = get_input('subject');
        if (!$subject)
        {
            throw new ValidationException(__('message:subject_missing'));
        }
        
        $emails = get_input('emails');        
        $emails_list = preg_split('/[\;\,\s]+/', $emails, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($emails_list as $email)
        {
            EmailAddress::validate($email);
        }
        
        if (!sizeof($emails_list))
        {
            throw new ValidationException(__('share:no_emails'));
        }
        
        $max_emails = 20;
        if (sizeof($emails_list) > $max_emails)
        {
            throw new ValidationException(strtr(__('share:too_many_emails'), array('{max}' => $max_emails)));
        }
         
        $message .= "\n\n".__('share:link_is').' '.$url;
        $message .= strtr("\n\n".__('share:disclaimer')."\n".__('share:disclaimer_2'), array(
            '{name}' => $user->name
        ));
         
        $mail = OutgoingMail::create($subject, $message);
        $mail->set_from_user($user);
        
        $sent_emails = array();
        $duplicate_emails = array();
        $time = timestamp();
        foreach ($emails_list as $email)
        {
            if (SharedEmail::query()
                ->where('email = ?', $email)
                ->where('url = ?', $url)
                ->where('time_shared > ?', $time - 86400)
                ->exists())
            {
                $duplicate_emails[] = $email;
            }
            else
            {
                $shared_email = new SharedEmail();
                $shared_email->user_guid = $user->guid;
                $shared_email->time_shared = $time;
                $shared_email->email = $email;
                $shared_email->url = $url;
                $shared_email->save();
                
                $mail->add_to($email);
                $sent_emails[] = $email;
            }
        }        

        if ($duplicate_emails)
        {
            $duplicate_error = __('share:duplicate') . " ". implode(', ', $duplicate_emails);
        }
        else
        {
            $duplicate_error = '';
        }
        
        if ($sent_emails)
        {        
            $mail->send();        
                    
            if ($duplicate_error)
            {
                SessionMessages::add_error($duplicate_error);
            }
        
            SessionMessages::add(
                ($mail->status == OutgoingMail::Held) ? __('message:held') : __('message:sent')
            );
            $this->redirect('/pg/blank');
        }
        else
        {
            throw new ValidationException($duplicate_error);
        }
    }

    function render()
    {
        $this->page_draw(array(
            'title' => __('share:email'),
            'content' => view('org/share', array(
                'org' => $this->get_org(),
                'url' => get_input('u')
            )),
            'theme_name' => 'editor',
            'no_top_bar' => true,            
        ));
    }
}
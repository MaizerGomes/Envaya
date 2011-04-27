<?php

class Action_Discussion_AddMessage extends Action
{
    function process_input()
    {
        $topic = $this->get_topic();                       
        $org = $this->get_org();

        $uniqid = get_input('uniqid');

        $duplicate = $topic->query_messages()
            ->with_metadata('uniqid', $uniqid)
            ->get();
        if ($duplicate)
        {
            return forward($topic->get_url());
        }               
        
        $name = get_input('name');
        if (!$name)
        {
            SessionMessages::add_error(__('discussions:name_missing'));
            return $this->render();
        }

        $content = get_input('content');
        if (!$content)
        {
            SessionMessages::add_error(__('discussions:content_missing'));
            return $this->render();
        }
        
        if (!$this->check_captcha())
        {
            return $this->render_captcha(array('instructions' => __('discussions:captcha_instructions')));
        }    

        $location = get_input('location');
        
        Session::set('user_name', $name);
        Session::set('user_location', $location);
        
        $user = Session::get_loggedin_user();
        
        $content = Markup::sanitize_html($content, array('Envaya.Untrusted' => !$user));
        
        $time = time();
        
        $message = new DiscussionMessage();
        $message->from_name = $name;
        $message->from_location = $location;
        $message->container_guid = $topic->guid;
        $message->subject = "RE: {$topic->subject}";            
        $message->time_posted = $time;
        $message->set_content($content, true);
        $message->set_metadata('uniqid', $uniqid);
        
        if ($user)
        {
            $message->from_email = $user->email;
            $message->owner_guid = $user->guid;            
        } 
        $message->save();
        
        if (!$user)
        {
            $message->set_session_owner();
        }
        
        $topic->refresh_attributes();
        $topic->save();    
        
        $message->post_feed_items();
        
        if ($org->is_notification_enabled(Notification::Discussion)
            && (!$user || $user->guid != $org->guid))
        {
            // notify site of message
            $mail = OutgoingMail::create(
                strtr(__('discussions:notification_subject', $org->language), array(
                    '{name}' => $message->from_name, '{topic}' => $topic->subject
                ))   
            );
            $mail->setBodyHtml(view('emails/discussion_message', array('message' => $message)));
            $mail->send_to_user($org);
        }
        
        SessionMessages::add_html(__('discussions:message_added')
            . view('discussions/invite_link', array('topic' => $topic)));        
        
        forward($topic->get_url());    
    }
    
    function render()
    {    
        $topic = $this->get_topic();            
        $this->use_public_layout();                
        
        $this->page_draw(array(
            'title' => __('discussions:title'),
            'content' => view("discussions/topic_add_message", array('topic' => $topic)),
        ));
    }
}    
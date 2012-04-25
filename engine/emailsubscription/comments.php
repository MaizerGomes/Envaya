<?php

class EmailSubscription_Comments extends EmailSubscription
{
    function send_notification($event_name, $comment)
    {
        if ($comment->owner_guid && $comment->owner_guid == $this->owner_guid)    
        {
            return;
        }
        
        if ($comment->email == $this->email)
        {
            return;
        }
        
        if ($event_name == Comment::Added)
        {
            $this->send(array(
                'notifier' => $comment,
                'reply_to' => EmailAddress::add_signed_tag(Config::get('mail:reply_email'), "comment{$comment->tid}"),
                'subject' => strtr(__('comment:notification_subject', $this->language), array(
                    '{name}' => $comment->get_name(),
                )), 
                'body' => view('emails/comment_added', array(
                    'comment' => $comment, 
                )),                    
            ));
        }
    }
    
    function get_description()
    {
        $container = $this->get_container_entity();               
        if ($container instanceof Widget)
        {
            return strtr(__('comment:page_subscription'), array('{url}' => $container->get_url()));
        }
        else if ($container instanceof User)
        {
            return strtr(__('comment:user_subscription'), array('{name}' => $container->name));
        }
        else if ($container instanceof UserScope)
        {
            return strtr(__('comment:scope_subscription'), array('{scope}' => $container->get_title()));
        }
        return '?';
    }
    
    static function handle_mail_reply($mail, $match)
    {
        $tid = $match['tid'];
        
        $comment = Comment::query()->where('tid = ?', $tid)->show_disabled(true)->get();
        if (!$comment)
        {
            error_log("invalid comment tid $tid");
            return false;
        }
        
        $widget = $comment->get_container_entity();
        if (!$widget)
        {
            error_log("invalid container for comment tid $tid");
            return false;
        }
        
        $parsed_address = EmailAddress::parse_address($mail->from);
        
        $reply = new Comment();
        $reply->container_guid = $widget->guid;    
        $reply->name = @$parsed_address['name'];
        $reply->email = @$parsed_address['address'];
        $reply->location = "via email";
        $reply->set_content(nl2br(escape(IncomingMail::strip_quoted_text($mail->text))), true);
        $reply->save();
                
        $widget->refresh_attributes();
		$widget->save();
        
        $reply->send_notifications(Comment::Added);        
                
        return true;
    }            
}

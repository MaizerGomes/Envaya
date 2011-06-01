<?php

/*
 * A template for an email message that can be sent to multiple users.
 * The message can contain placeholder strings with properties of a User or Organization,
 * (e.g. {{username}}), which will be replaced with the appropriate values for each user.
 */

class EmailTemplate extends Entity
{
    static $table_name = 'email_templates';

    static $table_attributes = array(
        'subject' => '',
        'from' => '',
        'num_sent' => 0,
        'time_last_sent' => 0,
    );
    
    static $mixin_classes = array(
        'Mixin_Content'
    );    
    
    static $allowed_placeholders = array(
        'username',
        'name',
        'email',
    );
    
    function update()
    {
        $this->num_sent = $this->query_outgoing_mail()->count();        
        $outgoing_mail = $this->query_outgoing_mail()->order_by('id desc')->get();
        $this->time_last_sent = $outgoing_mail ? $outgoing_mail->time_created : 0;
        $this->save();
    }
    
    function query_outgoing_mail()
    {
        return OutgoingMail::query()->where('email_guid = ?', $this->guid);
    }
    
    function query_potential_recipients()
    {
        return Organization::query()
            ->where('approval > 0')
            ->where("email <> ''")
            ->where('(notifications & ?) > 0', Notification::Batch)
            ->where("not exists (select * from outgoing_mail where email_guid = ? and to_guid = users.guid)", 
                $this->guid);        
    }
    
    function render($content, $user)
    {
        $args = array();
        if ($user)
        {
            foreach (static::$allowed_placeholders as $prop)
            {
                $value = $user->$prop;
                $args["{{".$prop."}}"] = $value;
                $args["%7B%7B".$prop."%7D%7D"] = $value; // {{ }} may be urlencoded 
            }
        }
   
        return strtr($content, $args);
    }    
    
    function render_content($user)
    {
        return $this->render($this->content, $user);
    }
    
    function render_subject($user)
    {
        return $this->render($this->subject, $user);
    }
    
    function get_outgoing_mail_for($user)
    {
        return $this->query_outgoing_mail()
            ->where('to_guid = ?', $user->guid)
            ->get();
    }
        
    function can_send_to($user)
    {    
        return $user && $user->email && $user->is_notification_enabled(Notification::Batch)        
            && $this->query_outgoing_mail()
                ->where('to_guid = ?', $user->guid)
                ->where('status <> ?', OutgoingMail::Failed)
                ->is_empty();
    }
    
    function send_to($user)
    {        
        $subject = $this->render_subject($user);
        $body = view('emails/template', array(
            'user' => $user, 
            'email' => $this
        ));

        $mail = OutgoingMail::create($subject);
        $mail->setBodyHtml($body);
        $mail->setFrom(Config::get('email_from'), $this->from);
        $mail->email_guid = $this->guid;
        $mail->send_to_user($user);
 
        $user->last_notify_time = time();
        $user->save();
    }
    
    function get_url()
    {
        return "/admin/contact/email/{$this->guid}";
    }
}
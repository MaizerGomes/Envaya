<?php

class EmailSubscription_Network extends EmailSubscription
{
    static $query_subtype_ids = array('core.subscription.email.network');

    function send_notification($event_name, $relationship)
    {
        if ($event_name == OrgRelationship::Added)
        {
            $reverse = $relationship->get_reverse_relationship();
            $org = $relationship->get_container_entity();
            $subject_org = $relationship->get_subject_organization();        
            $widget = $org->get_widget_by_class('Network');
           
            $subject = strtr($relationship->msg('notify_added_subject', $subject_org->language), array(
                '{name}' => $org->name, '{subject}' => $subject_org->name
            ));
                
            $body = view('emails/network_relationship_added', array(
                'relationship' => $relationship,
                'reverse' => $reverse,
                'widget' => $widget
            ));

            $mail = OutgoingMail::create($subject);
            $mail->from_guid = $org->guid;
                
            $this->send(array(
                'notifier' => $relationship,
                'body' => $body, 
                'mail' => $mail
            ));
        }
    }
    
    function get_description()
    {
        $user = $this->get_root_container_entity();    
        $tr = array('{name}' => $user->name);
        return strtr(__('email:subscribe_network'), $tr);
    }
}

<?php

class Action_Admin_ChangeOrgApproval extends Action
{
    function before()
    {
        $this->require_admin();
    }
     
    function process_input()
    {
        $guid = (int)get_input('org_guid');
        $org = Organization::get_by_guid($guid);

        if (!$org)
        {
            return $this->not_found();
        }
        
        $approvedBefore = $org->is_approved();

        $org->approval = (int)get_input('approval');

        $approvedAfter = $org->is_approved();

        $org->save();

        if (!$approvedBefore && $approvedAfter && $org->email)
        {
            OutgoingMail::create(
                __('email:orgapproved:subject', $org->language),
                view('emails/org_approved', array('org' => $org))
            )->send_to_user($org);
        }
        
        $org->send_relationship_emails();

        SessionMessages::add(__('approval:changed'));

        forward($org->get_url());
    }
}    
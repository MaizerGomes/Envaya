<?php

class Action_Registration_CreateAccount extends Action_Registration_CreateAccountBase
{
    function post_process_input()
    {        
        $org = $this->org;
        
        $prevInfo = Session::get('registration');            
        Session::set('registration', null);
                    
        $org->country = $prevInfo['country'];
        $org->set_design_setting('tagline', $org->get_location_text(false));        
        $org->save();

        $invite_code = Session::get('invite_code');
        Session::set('invite_code', null);
        if ($invite_code)
        {
            $this->update_existing_relationships($invite_code);
        }        
        
        forward("/org/new?step=3");    
    }
    
    protected function handle_validation_exception($ex)
    {
        SessionMessages::add_error($ex->getMessage());
        Session::save_input();
        forward(Config::get('secure_url')."org/new?step=2");    
    }

    private function update_existing_relationships($invite_code)
    {
        $org = $this->org;
    
        $invitedEmail = InvitedEmail::query()
            ->where('invite_code = ?', $invite_code)
            ->where('registered_guid = 0')
            ->get();
        
        if (!$invitedEmail)
        {
            return;
        }
        
        /*
         * only update existing relationships if we're fairly confident they refer to 
         * the newly registered organization.
         */
        $invitedAddress = $invitedEmail->email;                            
        if ($invitedAddress == $org->email)
        {
            $relationships = OrgRelationship::query()
                ->where('subject_guid = 0')
                ->where('subject_email = ?', $invitedAddress)
                ->filter();
                
            foreach ($relationships as $relationship)
            {
                $relationship->subject_guid = $org->guid;
                $relationship->save();
                
                $reverse = $relationship->make_reverse_relationship();
                $reverse->set_subject_approved();
                $reverse->set_self_approved(); // not really, but they can always delete it before creating their network page
                $reverse->save();                                                                            
            }        
        }
        
        $invitedEmail->registered_guid = $org->guid;
        $invitedEmail->save();                
    }    
}
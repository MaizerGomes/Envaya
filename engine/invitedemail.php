<?php

class InvitedEmail extends Model
{
    static $table_name = 'invited_emails';

    static $table_attributes = array(
        'email' =>  '',
        'registered_guid' => 0,
        'invite_code' => '', 
        'last_invited' => 0,
        'num_invites' => 0,
    );
    
    function generate_invite_code()
    {
        $this->invite_code = substr(generate_random_cleartext_password(), 0, 20);
    }
    
    static function get_by_email($email)
    {
        $invitedEmail = static::query()->where('email = ?', $email)->get();
        if (!$invitedEmail)
        {
            $invitedEmail = new InvitedEmail();
            $invitedEmail->email = $email;
            $invitedEmail->generate_invite_code();
        }            
        return $invitedEmail;
    }
    
    function mark_invite_sent()
    {
        $this->last_invited = time();
        $this->num_invites += 1;
        $this->save();
    }
    
    function can_send_invite()
    {
        if (Organization::query(true)->where('email = ?', $this->email)->count() > 0) 
        {
            // avoid inviting people who are already registered
            return false;
        }    
    
        if ($this->last_invited > time() - 24*60*60*30 || $this->num_invites >= 3)
        {
            // avoid annoying people with frequent or endless email invites
            return false;
        }
        return true;
    }
        
}
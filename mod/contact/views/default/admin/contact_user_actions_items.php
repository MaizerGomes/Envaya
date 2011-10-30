<?php

$user = $vars['user'];

$subscription = EmailSubscription_Contact::query_for_entity($user)->get();
if ($subscription)
{
    echo "<a href='/admin/contact/email/subscription/{$subscription->guid}'>".
        sprintf(__('contact:send_template'), __('contact:email'))."</a>";
}

$subscription = SMSSubscription_Contact::query_for_entity($user)->get();
if ($subscription)
{
    echo "<a href='/admin/contact/sms/subscription/{$subscription->guid}'>".
        sprintf(__('contact:send_template'), __('contact:sms'))."</a>";
}

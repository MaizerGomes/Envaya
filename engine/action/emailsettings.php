<?php

class Action_EmailSettings extends Action
{
    private function verify_access($email, $code, $subscriptions)
    {
        if (!$email || $code != EmailSubscription::get_email_fingerprint($email) || sizeof($subscriptions) == 0)
        {
            throw new RedirectException(__("user:notification:invalid"), "/pg/login");
        }
    }

    function process_input()
    {
        $email = get_input('email');
        $code = get_input('code');
        
        $all_subscription_ids = get_input_array('subscriptions');
        $enabled_subscription_ids = get_input_array('enabled_subscriptions');
        
        $subscriptions = EmailSubscription::query()
            ->where('email = ?', $email)
            ->where_in('guid', $all_subscription_ids)
            ->show_disabled(true)
            ->filter();
        
        $this->verify_access($email, $code, $subscriptions);

        foreach ($subscriptions as $subscription)
        {
            $subscription->set_status(
                in_array($subscription->guid, $enabled_subscription_ids) ?
                    Entity::Enabled : Entity::Disabled                
            );            
            $subscription->save();                        
        }
        SessionMessages::add(__('user:notification:success'));

        $this->redirect();
    }

    function render()
    {
        $email = get_input('e');
        $code = get_input('c');
        $id = get_input('id');
        
        $offset = (int)get_input('offset');
        
        $limit = 15;
        $show_more = false;
        
        $query = EmailSubscription::query()
            ->where('email = ?', $email)
            ->show_disabled(true)
            ->order_by('guid')
            ->limit($limit, $offset);
        
        if ($id)
        {
            $query->where('guid = ?', $id);
            $show_more = true;
        }        
        
        $count = $query->count();
        
        $subscriptions = $query->filter();        
        
        $this->verify_access($email, $code, $subscriptions);
        
        $this->page_draw(array(
            'title' => __("user:notification:label"),
            'content' => view('account/email_settings', array(
                'email' => $email, 
                'limit' => $limit,
                'count' => $count,
                'offset' => $offset,
                'subscriptions' => $subscriptions,
                'show_more' => $show_more
            ))
        ));
    }
}
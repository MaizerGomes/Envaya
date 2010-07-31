<?php

class Controller_Admin extends Controller
{
    function before()
    {
        $this->require_admin();
        set_context('editor');
        set_theme('editor');

    }

    function action_contact()
    {
        $title = __('email:send');
        $area1 = elgg_view('admin/contact');
        $body = elgg_view_layout("one_column_wide", elgg_view_title($title), $area1);
        $this->page_draw($title,$body);
    }


    function action_confirm_email()
    {
        $title = __('email:send');
        $org = get_user_by_username(get_input('username'));

        if ($org)
        {
            $area1 = elgg_view('admin/sendEmail', array('org' => $org, 'from' => get_input('from')));

            $body = elgg_view_layout("one_column", elgg_view_title($title), $area1);

            $this->page_draw($title,$body);
        }
        else
        {
            not_found();
        }
    }

    function action_view_email()
    {
        $user = get_user_by_username(get_input('username') ?: 'envaya');

        if ($user)
        {
            echo elgg_view('emails/reminder', array('org' => $user));
        }
        else
        {
            not_found();
        }
    }

    function action_send_email()
    {
        $this->validate_security_token();

        /*
        $orgs = Organization::filterByCondition(
            array("approval > 0 AND notify_days > 0 AND ((last_notify_time IS NULL) OR (last_notify_time + notify_days * 86400 < ?)) AND email <> ''"),
            array($time), '', 1);
        */

        global $CONFIG;

        $org = get_entity(get_input('org_guid'));

        if ($org && $org->email && $org->notify_days > 0 && $org->approval > 0
            && (!$org->last_notify_time || $org->last_notify_time + $org->notify_days * 86400 < time())
            )
        {
            $subject = __('email:reminder:subject', $org->language);

            $body = elgg_view('emails/reminder', array('org' => $org));

            $headers = array(
                'To' => $org->getNameForEmail(),
                'Content-Type' => 'text/html'
            );

            send_mail($org->email, $subject, $body, $headers);

            $org->last_notify_time = time();
            $org->save();

            system_message(__('email:reminder:sent'));
        }
        else
        {
            register_error(__('email:reminder:none'));
        }

        forward(get_input('from') ?: "/admin/contact");
    }

    function action_translateQueue()
    {
        $title = __('translate:queue');

        $body = elgg_view_layout("one_column_padded", elgg_view_title($title),
            elgg_view('translate/queue', array('lang' => get_language()))
        );

        $this->page_draw($title,$body);
    }


    function action_statistics()
    {
        $title = __("admin:statistics");
        $this->page_draw($title,
            elgg_view_layout("one_column_padded", elgg_view_title($title), elgg_view("admin/statistics")));
    }

    function action_user()
    {
        $search = get_input('s');
        $limit = get_input('limit', 10);
        $offset = get_input('offset', 0);

        $title = elgg_view_title(__('admin:user'));

        $result = list_entities('user', '', 0, $limit, false);

        $this->page_draw(
            __("admin:user"),
            elgg_view_layout("one_column_padded", $title,  elgg_view("admin/user") . $result)
        );
    }

    function action_search()
    {
        // Get input
        $tag = stripslashes(get_input('tag'));
        $subtype = stripslashes(get_input('subtype'));
        if (!$objecttype = stripslashes(get_input('object'))) {
            $objecttype = "";
        }
        if (!$md_type = stripslashes(get_input('tagtype'))) {
            $md_type = "";
        }
        $owner_guid = (int)get_input('owner_guid',0);
        if (substr_count($owner_guid,',')) {
            $owner_guid_array = explode(",",$owner_guid);
        } else {
            $owner_guid_array = $owner_guid;
        }

        if (empty($objecttype) && empty($subtype)) {
            $title = sprintf(__('search:title_with_query'),$tag);
        } else {
            if (empty($objecttype)) $objecttype = 'object';
            $itemtitle = 'item:' . $objecttype;
            if (!empty($subtype)) $itemtitle .= ':' . $subtype;
            $itemtitle = __($itemtitle);
            $title = sprintf(__('advancedsearchtitle'),$itemtitle,$tag);
        }

        if (!empty($tag)) {
            $body = "";
            $body .= elgg_view_title($title); // elgg_view_title(sprintf(__('search:title_with_query'),$tag));
            $body .= trigger_plugin_hook('search','',$tag,"");
            $body = elgg_view_layout('one_column_padded','',$body);
        }

        $this->page_draw($title,$body);

    }

    function action_logbrowser()
    {
        $limit = get_input('limit', 40);
        $offset = get_input('offset');

        $search_username = get_input('search_username');
        if ($search_username) {
            if ($user = get_user_by_username($search_username)) {
                $user = $user->guid;
            }
        } else {
            $user_guid = get_input('user_guid',0);
            if ($user_guid) {
                $user = (int) $user_guid;
            } else {
                $user = "";
            }
        }

        $timelower = get_input('timelower');
        if ($timelower) $timelower = strtotime($timelower);
        $timeupper = get_input('timeupper');
        if ($timeupper) $timeupper = strtotime($timeupper);

        $title = elgg_view_title(__('logbrowser'));

        // Get log entries
        $log = get_system_log($user, "", "", "","", $limit, $offset, false, $timeupper, $timelower);
        $count = get_system_log($user, "", "", "","", $limit, $offset, true, $timeupper, $timelower);
        $log_entries = array();

        foreach ($log as $l)
        {
            $tmp = new ElggObject();
            $tmp->subtype = T_logwrapper;
            $tmp->entry = $l;
            $log_entries[] = $tmp;
        }

        $form = elgg_view('logbrowser/form',array('user_guid' => $user, 'timeupper' => $timeupper, 'timelower' => $timelower));

        $result = elgg_view_entity_list($log_entries, $count, $offset, $limit, false, false);


        $this->page_draw(__('logbrowser'),elgg_view_layout("one_column_padded", $title,  $form . $result));

    }

    function action_add_user()
    {
        $this->validate_security_token();

        global $CONFIG;

        $username = get_input('username');
        $password = get_input('password');
        $password2 = get_input('password2');
        $email = get_input('email');
        $name = get_input('name');

        $admin = get_input('admin');
        if (is_array($admin)) $admin = $admin[0];

        if ($password != $password2)
        {
            action_error(__('create:passwords_differ'));
        }

        try
        {
            $new_user = register_user($username, $password, $name, $email, true);
            if ($admin != null)
            {
                $new_user->admin = true;
            }

            $new_user->admin_created = true;
            $new_user->created_by_guid = get_loggedin_userid();
            $new_user->save();

            notify_user($new_user->guid, $CONFIG->site_guid, __('useradd:subject'), sprintf(__('useradd:body'), $name, $CONFIG->sitename, $CONFIG->url, $username, $password));

            system_message(sprintf(__("adduser:ok"),$CONFIG->sitename));
        }
        catch (RegistrationException $r)
        {
            action_error($r->getMessage());
        }

        forward_to_referrer();
    }

    function action_approve()
    {
        $this->validate_security_token();

        $guid = (int)get_input('org_guid');
        $entity = get_entity($guid);

        if (($entity) && ($entity instanceof Organization))
        {
            $approvedBefore = $entity->isApproved();

            $entity->approval = (int)get_input('approval');

            $approvedAfter = $entity->isApproved();

            $entity->save();

            if (!$approvedBefore && $approvedAfter && $entity->email)
            {
                notify_user($entity->guid, $CONFIG->site_guid,
                    __('email:orgapproved:subject', $entity->language),
                    sprintf(__('email:orgapproved:body', $entity->language),
                        $entity->name,
                        $entity->getURL(),
                        "{$CONFIG->url}pg/dashboard",
                        __('help:title', $entity->language),
                        "{$CONFIG->url}org/help"
                    ),
                    NULL, 'email');
            }

            system_message(__('approval:changed'));
        }
        else
        {
            register_error(__('approval:notapproved'));
        }

        forward($entity->getUrl());

    }

    function action_delete_entity()
    {
        $this->validate_security_token();

        $guid = get_input('guid');
        $entity = get_entity($guid);

        if ($entity)
        {
            if ($entity->delete())
                system_message(sprintf(__('entity:delete:success'), $guid));
            else
                register_error(sprintf(__('entity:delete:fail'), $guid));
        }
        else
            register_error(sprintf(__('entity:delete:fail'), $guid));

        $next = get_input('next');
        if ($next)
        {
            forward($next);
        }
        else
        {
            forward_to_referrer();
        }
    }
    
    function action_add_featured()
    {
        $username = get_input('username');
        $user = get_user_by_username($username);
        if ($user)
        {
            $title = __('featured:add');
            $body = elgg_view('admin/add_featured', array('entity' => $user));
            $this->page_draw($title, elgg_view_layout("one_column_padded", 
                elgg_view_title($title), $body));        
        }
        else
        {
            not_found();
        }
    }
    
    function action_activate_featured()
    {
        $this->validate_security_token();
        
        $guid = get_input('guid');
        $entity = get_entity($guid);
        
        if ($entity && $entity instanceof FeaturedSite)
        {
            $activeSites = FeaturedSite::filterByCondition(array('active<>0'));
            
            $entity->active = 1;
            $entity->save();
            
            foreach ($activeSites as $activeSite)
            {
                $activeSite->active = 0;
                $activeSite->save();
            }
            forward('org/featured');
        }
        else        
        {   
            not_found();
        }

    }
    
    function action_new_featured()
    {
        $this->validate_security_token();
    
        $username = get_input('username');
        $user = get_user_by_username($username);
        if ($user)
        {
            $featuredSite = new FeaturedSite();
            $featuredSite->container_guid = $user->guid;
            $featuredSite->setContent(get_input('content'), true);
            $featuredSite->save();
            system_message('featured:created');
            forward('org/featured');
        }
        else
        {
            not_found();
        }
    }
    
    function action_save_featured()
    {
        $this->validate_security_token();
    
        $featuredSite = get_entity(get_input('guid'));
        if ($featuredSite && $featuredSite instanceof FeaturedSite)
        {
            $featuredSite->setContent(get_input('content'), true);
            $featuredSite->save();
            system_message('featured:saved');
            forward('org/featured');
        }
        else
        {
            not_found();
        }
    }    
    
    function action_edit_featured()
    {
        $guid = get_input('guid');
        $featuredSite = get_entity($guid);
        if ($featuredSite && $featuredSite instanceof FeaturedSite)
        {
            $title = __('featured:edit');
            $body = elgg_view('admin/edit_featured', array('entity' => $featuredSite));
            $this->page_draw($title, elgg_view_layout("one_column_padded", 
                elgg_view_title($title), $body));        
        }
        else
        {
            not_found();
        }
    }
}
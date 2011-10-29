<?php

/*
 * Controller for actions that are only accessible to site administrators.
 *
 * URL: /admin/<action>
 */
class Controller_Admin extends Controller
{
    static $routes = array(
        array(
            'regex' => '/entity/(?P<entity_guid>\d+)(/(?P<action>\w+))?\b',
            'defaults' => array('action' => 'view'),
            'action' => 'entity_<action>',
            'before' => 'init_entity_from_guid',
        ),    
        array(
            'regex' => '/(?P<action>\w+)\b',
        ),        
    );

    function before()
    {
        Permission_UseAdminTools::require_any();
        $this->page_draw_vars['theme_name'] = 'editor';
    }
    
    function init_entity_from_guid()
    {
        $guid = $this->param('entity_guid');
        
        $entity = Entity::get_by_guid($guid, true);
        if (!$entity)
        {
            throw new NotFoundException();
        }
        
        $this->params['entity'] = $entity;
    }
    
    function action_resend_mail()
    {
        $action = new Action_Admin_ResendMail($this);
        $action->execute();        
    }
    
    function action_resend_sms()
    {
        $action = new Action_Admin_ResendSMS($this);
        $action->execute();        
    }    
    
    function action_set_mail_status()
    {
        $action = new Action_Admin_SetMailStatus($this);
        $action->execute();        
    }    
    
    function action_subscriptions()
    {
        $action = new Action_Admin_Subscriptions($this);
        $action->execute();
    }
    
    function action_view_mail()
    {
        $id = (int)get_input('id');
        $mail = OutgoingMail::query()->where('id = ?', $id)->get();
        if (!$mail)
        {
            throw new NotFoundException();
        }
        
        $this->page_draw(array(
            'title' => __('email:view'),
            'content' => view('admin/view_mail', array('mail' => $mail))
        ));
    }
    
    function action_view_sms()
    {
        $id = (int)get_input('id');
        $sms = OutgoingSMS::query()->where('id = ?', $id)->get();
        if (!$sms)
        {
            throw new NotFoundException();
        }
        
        $this->page_draw(array(
            'title' => __('sms:view'),
            'content' => view('admin/view_sms', array('sms' => $sms))
        ));
    }    
    
    function action_recent_photos()
    {
        $this->page_draw(array(
            'title' => 'Recent Photos',
            'content' => view('admin/recent_content', array('content_filter' => "%<img%")),
            'theme_name' => 'editor',
        ));             
    }

    function action_recent_documents()
    {
        $this->page_draw(array(
            'title' => 'Recent Documents',
            'content' => view('admin/recent_content', array('content_filter' => "%<scribd%")),
            'theme_name' => 'editor',
        ));             
    }

    
    function action_outgoing_mail()
    {
        $this->page_draw(array(
            'title' => __('email:outgoing_mail'),
            'content' => view('admin/outgoing_mail'),
            'theme_name' => 'simple_wide',
            'header' => '',
        ));        
    }   
    
    function action_outgoing_sms()
    {
        $this->page_draw(array(
            'title' => __('sms:outgoing_sms'),
            'content' => view('admin/outgoing_sms'),
            'theme_name' => 'simple_wide',
            'header' => '',
        ));        
    }       
    
    function action_statistics()
    {
        $this->page_draw(array(
            'title' => __("admin:statistics"),
            'content' => view("admin/statistics")
        ));
    }
    
    function action_logbrowser()
    {
        $query = SystemLog::query();
    
        $limit = get_input('limit', 40);
        $offset = get_input('offset');

        $search_username = get_input('search_username');
        if ($search_username) {
            if ($user = User::get_by_username($search_username)) {
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
        if ($timelower) 
        {
            $query->where('time_created > ?', strtotime($timelower));
        }
        $timeupper = get_input('timeupper');
        if ($timeupper) 
        {
            $query->where('time_created < ?', strtotime($timeupper));
        }

        if ($user)
        {
            $query->where('user_guid=?', $user);
        }
                
        $query->limit($limit, $offset);
        
        $log = $query->filter();

        $this->page_draw(array(
            'title' => __('logbrowser'),
            'content' => view('admin/log_browse', array(
                'user_guid' => $user, 
                'timeupper' => $timeupper, 
                'timelower' => $timelower,
                'baseurl' => $_SERVER['REQUEST_URI'],
                'offset' => $offset,
                'count' => null,
                'count_displayed' => sizeof($log),
                'limit' => $limit,
                'entries' => $log
            ))
        ));
    }    

    function entity_disable()
    {
        $action = new Action_Admin_DisableEntity($this);
        $action->execute();
    }            

    function entity_enable()
    {
        $action = new Action_Admin_EnableEntity($this);
        $action->execute();
    }
    
    function entity_view()
    {
        $action = new Action_Admin_ViewEntity($this);
        $action->execute();
    }
    
    function action_entities()
    {
        $root_entity = UserScope::get_root();
        if (!$root_entity)
        {
            throw new NotFoundException();
        }        
        $this->redirect("/admin/entity/{$root_entity->guid}");
    }
}
<?php

/*
 * Controller for the top level of a user's site.
 *
 * widget_name may refer to an actual widget name for pre-defined widgets
 * (an alias for /<username>/page/<widget_name>[/<action>])
 *
 * URL: /<username>[/<widget_name>[/<action>]]
 */
class Controller_UserSite extends Controller_User
{
    static $routes = array(
        array(
            'regex' => '(/)?$',
            'action' => 'action_index',
        ),
        array(
            'regex' => '/(post|widget|node)/(?P<container_guid>\w+)\.(?P<widget_name>\w+)',
            'controller' => 'Controller_Widget',
            'before' => 'init_widget_from_container',
        ),
        array(
            'regex' => '/(post|widget|node)/((?P<slug>[\w\-]+)\,)?(?P<widget_local_id>\d+)',
            'controller' => 'Controller_Widget',
            'before' => 'init_widget_from_local_id',
        ),    
        array(
            'regex' => '/page/(?P<widget_name>[\w\-]+)',
            'controller' => 'Controller_Widget',
            'before' => 'init_widget_from_name',
        ),
        array(
            'regex' => '/(?P<action>\w+)\b',
        ),
        array(
            'regex' => '/(?P<widget_name>[\w\-]+)(/(?P<action>\w+))?',
            'defaults' => array('action' => 'view'),
            'action' => 'action_widget_<action>',
        )
    );

    function action_index()
    {
        $user = $this->get_user();
        $this->page_draw_vars['is_site_home'] = true;            
        $home_widget = $user->query_menu_widgets()->get();             
        return $this->index_widget($home_widget);
    }
    
    protected function init_widget_from_local_id()
    {
        $user = $this->get_user();
        $local_id = $this->param('widget_local_id');
        
        return $this->init_widget(Widget::query()
            ->where('user_guid = ?', $user->guid)
            ->where('local_id = ?', $local_id)
            ->show_disabled(true)
            ->get()
        );
    }
     
    protected function init_widget_from_container()
    {
        $container_guid = $this->param('container_guid');
        $widget_name = $this->param('widget_name');
     
        $container = Widget::get_by_guid($container_guid, true);
        
        $widget = null;
        
        if ($container)
        {
            $widget = $container->get_widget_by_name($widget_name);
            if (!$widget)
            {
                $cls = $container->get_default_widget_class_for_name($widget_name);                
                if ($cls)
                {
                    $widget = $cls::new_for_entity($container, array('widget_name' => $widget_name));
                }
            }            
        }
        
        return $this->init_widget($widget);
    }    

    protected function init_widget_from_name()
    {
        $widget_name = $this->param('widget_name');
        
        $user = $this->get_user();
        
        $widget = $user->get_widget_by_name($widget_name);
        if (!$widget)
        {
            $cls = $user->get_default_widget_class_for_name($widget_name);                
            if ($cls)
            {
                $widget = $cls::new_for_entity($user, array('widget_name' => $widget_name));
            }
        }
        
        $this->init_widget($widget);
    }    
     
    protected function init_widget($widget)
    {
        if ($widget && $widget->get_container_user()->guid == $this->get_user()->guid)
        {
            $this->params['widget'] = $widget;
        }
        else
        {
            throw new NotFoundException();
        }       
    }    
    
    function action_widget_view()
    {    
        $user = $this->get_user();
        $widgetName = $this->param('widget_name');        
               
        $widget = $user->get_widget_by_name($widgetName);                        
        
        $home_widget = $user->query_menu_widgets()->get();
        if ($home_widget && $widget && $home_widget->guid == $widget->guid)
        {                
            $this->page_draw_vars['is_site_home'] = true;
        }
        
        return $this->index_widget($widget);
    }
        
    function action_widget_edit()
    {    
        // backwards compatibility to avoid breaking links and allow editing widgets
        // at /<username>/<widget_name>/edit         
        // by forwarding to new URLs at /<username>/page/<widget_name>/edit         
     
        $user = $this->get_user();
        
        $widgetName = $this->param('widget_name');
        $widget = $user->get_widget_by_name($widgetName);
        
        if (!$widget || !$widget->is_enabled())
        {
            throw new NotFoundException();            
        }
        
        Permission_EditUserSite::require_for_entity($widget);
        
        $this->redirect($widget->get_edit_url());
    }

    function action_dashboard()
    {    
        $action = new Action_User_Dashboard($this);
        $action->execute();
    }
    
    function action_password()
    {
        $action = new Action_User_ChangePassword($this);
        $action->execute();    
    }

    function action_username()
    {
        $action = new Action_User_ChangeUsername($this);
        $action->execute();
    }

    function action_settings()
    {    
        $action = new Action_User_Settings($this);
        $action->execute();
    }

    function action_addphotos()
    {
        $action = new Action_User_AddPhotos($this);
        $action->execute();        
    }
            
    function action_send_message()
    {
        $action = new Action_User_SendMessage($this);
        $action->execute();   
    }    
    
    function action_domains()
    {
        $user = $this->get_user();
        
        Permission_UseAdminTools::require_for_entity($user);        
        
        $this->use_editor_layout();
        
        $this->page_draw(array(
            'title' => __('domains:edit'),
            'content' => view('account/domains', array('user' => $user)),
        ));
    }
    
    function action_add_domain()
    {
        $action = new Action_User_AddDomain($this);
        $action->execute();        
    }
    
    function action_delete_domain()
    {
        $action = new Action_User_DeleteDomain($this);
        $action->execute();
    }

    function action_share()
    {
        $action = new Action_User_Share($this);
        $action->execute();
    }
    
    function action_custom_design()
    {
        $action = new Action_User_CustomDesign($this);
        $action->execute();
    }
    
    function action_add_page()
    {
        $action = new Action_Widget_Add($this, $this->get_user());
        $action->execute();
    }
        
    function action_design()
    {
        $action = new Action_User_Design($this);
        $action->execute();
    }

    function action_set_approval()
    {
        $action = new Action_User_ChangeApproval($this);
        $action->execute();
    }               
}
<?php

/*
 * Base class for controllers that work in the context of a User's site.
 * The User is determined from the username in the URL.
 *
 * URL: /<username>[...]
 */
abstract class Controller_User extends Controller
{
    static $routes = array();

    protected $public_layout = false;
        
    function get_user()
    {
        return $this->param('user');
    }       
        
    private function get_approval_message()
    {
        $user = $this->get_user();    
        switch ($user->approval)
        {
            case User::AwaitingApproval:    return __('approval:waiting');
            case User::Rejected:            return __('approval:rejected');
            default:                        return null;
        }        
    }
   
    public function execute($uri)
    {
        try
        {           
            parent::execute($uri);
        }
        catch (NotFoundException $ex)
        {
            $this->not_found();
        }
    }   
   
    function index_widget($widget)
    {
        $user = $this->get_user();
    
        $this->prefer_http();      
        $this->allow_content_translation();        
        $this->use_public_layout($widget);        
        
        if ($widget && $widget->is_enabled())
        {
            $this->allow_view_types($widget->get_view_types());
        }
        else
        {
            $this->allow_view_types(null);
        }
                        
        if (!$widget || !$widget->is_enabled() || $widget->publish_status != Widget::Published)
        {
            throw new NotFoundException();
        }
        
        Permission_ViewUserSite::require_for_entity($widget);
                
        if (Permission_EditUserSite::has_for_entity($widget))
        {
            PageContext::get_submenu('edit')->add_item(__("widget:edit"), $widget->get_edit_url());
        }
        
        if (Permission_UseAdminTools::has_for_entity($widget))
        {
            PageContext::get_submenu('org_actions')->add_item(__('widget:options'), "{$widget->get_base_url()}/options");
        }
        
        $container = $widget->get_container_entity();         
        $content = $container->render_child_view($widget, array('is_primary' => true));
        
        $this->page_draw(array(
            'content' => $content,
            'title' => $widget->get_title(),
            'show_next_steps' => $user->equals(Session::get_logged_in_user()),
        )); 
    }
           
    function use_public_layout($cur_widget = null)
    {
        $user = $this->get_user();
                
        $this->public_layout = true;
                
        $theme_name = get_input("__theme") ?: $user->get_design_setting('theme_name') ?: Config::get('fallback_theme');
        
        $this->page_draw_vars['design'] = $user->get_design_settings();
        $this->page_draw_vars['tagline'] = $user->get_design_setting('tagline');
        $this->page_draw_vars['theme_name'] = $theme_name;
        $this->page_draw_vars['site_name'] = $user->name;
        $this->page_draw_vars['site_username'] = $user->username;
        $this->page_draw_vars['site_approved'] = $user->is_approved();
        $this->page_draw_vars['site_url'] = $user->get_url();     
        $this->page_draw_vars['logo'] = view('account/icon', array('user' => $user, 'size' => 'medium'));          
        $this->page_draw_vars['login_url'] = url_with_param($this->full_rewritten_url(), 'login', 1);
        
        if (Views::get_request_type() == 'default')
        {
            $theme = Theme::get($theme_name);            
            Views::set_current_type($theme->get_viewtype());         
        }
        
        $this->show_site_menu($cur_widget);
    }
    
    private function show_site_menu($cur_widget)
    {
        $user = $this->get_user();
        
        $widgets = $user->query_menu_widgets()
            ->columns('guid,container_guid,owner_guid,language,widget_name,subclass,handler_arg,title')
            ->filter();
        
        foreach ($widgets as $widget)
        {
            $is_selected = $cur_widget && $cur_widget->guid == $widget->guid;
        
            PageContext::get_submenu()->add_item(
                $widget->get_title(), 
                $widget->get_url(),
                $is_selected
            );
        }        
    }

    function use_editor_layout()
    {
        $this->page_draw_vars['theme_name'] = 'editor';
    }

    protected function get_messages($vars)
    {
        $vars['user'] = $this->get_user();        
        return view('messages/usersite', $vars);        
    }
                
    public function prepare_page_draw_vars(&$vars)
    {
        $user = $this->get_user();
        
        $is_public = $this->public_layout;        
        if ($is_public)
        {    
            $approval_message = $this->get_approval_message();
            if ($approval_message)
            {
                SessionMessages::add($approval_message);
            }
            
            $vars['messages'] = $this->get_messages($vars);            
            $vars['header'] = '';
        }
        
        parent::prepare_page_draw_vars($vars);        
        
        if ($is_public)
        {            
            $vars['header'] = view('page_elements/site_header', $vars);
        }
    }	
            
    public function not_found()
    {
        $uri_part = $this->param('user_uri');
        $user = $this->get_user();
        $redirect_url = NotFoundRedirect::get_redirect_url($uri_part, $user);
        if ($redirect_url)
        {
            $this->redirect($user->get_url() . $redirect_url);
        }
        else
        {
            parent::not_found();
        }
    }   
}

<?php

/*
 * Controller for allowing users to contribute translations.
 *
 * URL: /tr/...
 */
class Controller_Translate extends Controller
{
    static $routes = array(
        array(
            'regex' => '(/)?$', 
            'action' => 'action_index',
        ),
        array(
            'regex' => '/(?P<action>instructions|delete_comment)\b', 
        ),                
        array(
            'regex' => '/admin\b', 
            'controller' => 'Controller_TranslateAdmin',
        ),        
        array(
            'regex' => '/(?P<lang>\w+)/translators/(?P<guid>\d+)\b', 
            'action' => 'action_translator',
            'before' => 'init_language',
        ),        
        array(
            'regex' => '/(?P<lang>\w+)/translators(/)?$', 
            'action' => 'action_translators',
            'before' => 'init_language',
        ),
        array(
            'regex' => '/(?P<lang>\w+)/?$', 
            'action' => 'action_view_language',
            'before' => 'init_language',
        ),        
        array(
            'regex' => '/(?P<lang>\w+)/(?P<action>\w+)$', 
            'before' => 'init_language',
        ),
        array(
            'regex' => '/(?P<lang>\w+)/module/(?P<group_name>\w+)(/)?$', 
            'action' => 'action_view_group',
            'before' => 'init_language_group',
        ),
        array(
            'regex' => '/(?P<lang>\w+)/module/(?P<group_name>\w+)/(?P<key_name>[\w\%\:]+)', 
            'controller' => 'Controller_TranslateKey',
            'before' => 'init_language_group_key',
        ),
    );
    
    function before()
    {
        $this->page_draw_vars['theme_name'] = 'simple_wide';
        $this->page_draw_vars['login_url'] = url_with_param(Request::full_original_url(), 'login', 1);
    }

    function init_language()
    {
        $code = $this->param('lang');
        $language = InterfaceLanguage::query()->where('code = ?', $code)->get();
        if (!$language)
        {
            throw new NotFoundException();
        }
        $this->params['language'] = $language;
    }
        
    function init_language_group()
    {
        $this->init_language();
        $language = $this->param('language');
        $group_name = $this->param('group_name');
        
        $group = $language->query_groups()->where('name = ?', $group_name)->get();
        if (!$group)
        {
            throw new NotFoundException();
        }
        $this->params['group'] = $group;
    }
    
    function init_language_group_key()
    {
        $this->init_language_group();
        $key_name = $this->param('key_name');
        $group = $this->param('group');
        
        $key = $group->get_key_by_name($key_name);
        if (!$key)
        {
            throw new NotFoundException();
        }
        $this->params['key'] = $key;
    }
    
    function action_index()
    {
        return $this->page_draw(array(
            'title' => __('itrans:translations'),
            'header' => view('translate/header'),
            'content' => view('translate/interface_languages')
        ));
    }
     
    function action_instructions()
    {
        return $this->page_draw(array(
            'title' => __('itrans:instructions'),
            'header' => view('translate/header', array('title' => __('itrans:instructions'))),
            'content' => view('translate/instructions')
        ));
    }
     
    function action_view_language()
    {
        $language = $this->param('language');
        
        return $this->page_draw(array(
            'title' => __('itrans:translations'),
            'header' => view('translate/header', array('items' => array($language))),
            'content' => view('translate/interface_language', array('language' => $language))
        ));
    }           
    
    function action_translators()
    {
        $language = $this->param('language');
    
        return $this->page_draw(array(
            'title' => __('itrans:translators'),
            'header' => view('translate/header', array('items' => array($language), 'title' => __('itrans:translators'))),
            'content' => view('translate/translators', array('language' => $language))
        ));        
    }
    
    function action_translator()
    {
        $language = $this->param('language');
    
        $user = User::get_by_guid($this->param('guid'));
        if (!$user)
        {
            throw new NotFoundException();
        }
        $stats = $language->get_stats_for_user($user);
        if (!$stats->guid)
        {
            throw new NotFoundException();
        }
    
        return $this->page_draw(array(
            'title' => __('itrans:translator'),
            'header' => view('translate/header', array('items' => array(
                $language, 
                array('title' => __('itrans:translators'), 'url' => "{$language->get_url()}/translators"), 
                array('title' => $stats->get_display_name())
            ))),
            'content' => view('translate/translator', array('language' => $language, 'user' => $user, 'stats' => $stats))
        ));        
    }
    
    function action_latest()
    {
        $language = $this->param('language');
        
        return $this->page_draw(array(
            'title' => __('itrans:latest'),
            'header' => view('translate/header', array('items' => array($language), 'title' => __('itrans:latest'))),
            'content' => view('translate/latest_translations', array('language' => $language))
        ));    
    }
    
    function filter_keys($keys)
    {
        $query = get_input('q');
        $status = get_input('status');    
        
        if ($query || $status)
        {
            Session::set('translate_filter_query', $query);
            Session::set('translate_filter_status', $status);
        }
        else
        {
            $query = Session::get('translate_filter_query');
            $status = Session::get('translate_filter_status');
        }
    
        $filtered_keys = array();
        
        foreach ($keys as $key)
        {
            $empty = ($key->best_translation == '');
        
            if ($status == 'empty' && !$empty)
            {
                continue;
            }
            if ($status == 'notempty' && $empty)
            {
                continue;
            }
        
            if ($query)
            {
                $lq = strtolower($query);
                if (strpos($key->name, $lq) === false
                    && strpos(strtolower(__($key->name)), $lq) === false
                    && strpos(strtolower($key->best_translation), $lq) === false)
                {
                    continue;
                }
            }
            
            $filtered_keys[] = $key;
        }
        return $filtered_keys;        
    }

    function action_view_group()
    {
        PageContext::add_header_html('<meta name="robots" content="noindex,nofollow" />');
    
        $group = $this->param('group');
        $language = $this->param('language');
            
        $keys = $group->get_available_keys();
        $filtered_keys = $this->filter_keys($keys);
        
        return $this->page_draw(array(
            'title' => __('itrans:translations'),
            'header' => view('translate/header',  array('items' => array($language, $group))),
            'content' => view('translate/interface_group', array(
                'group' => $group,
                'all_keys' => $keys,
                'filtered_keys' => $filtered_keys,
            ))
        ));       
    }    
    
    function action_delete_comment()
    {
        $this->validate_security_token();    
        
        $guid = (int)get_input('comment');
        $comment = InterfaceKeyComment::get_by_guid($guid);
        if (!$comment || !$comment->can_edit())
        {
            throw new RedirectException(__('comment:not_deleted'));
        }
        
        $comment->disable();
        $comment->save();

        $container = $comment->get_container_entity();
        SessionMessages::add(__('comment:deleted'));            
        $this->redirect($container->get_url());
    }    
    
    function action_comments()
    {
        $language = $this->param('language');
        
        return $this->page_draw(array(
            'title' => __('itrans:latest_comments'),
            'header' => view('translate/header', array('items' => array($language), 'title' => __('itrans:latest_comments'))),
            'content' => view('translate/latest_comments', array('language' => $language))
        ));            
    }
    
    function action_page()
    {
        PageContext::add_header_html('<meta name="robots" content="noindex,nofollow" />');
    
        $language = $this->param('language');
    
        $b64_keys = get_input('keys');
        $gz_keys = base64_decode($b64_keys);
        $key_names = explode(",",gzuncompress($gz_keys));
        
        return $this->page_draw(array(
            'title' => __('itrans:edit_page'),
            'header' => view('translate/header', array('items' => array($language), 'title' => __('itrans:edit_page'))),
            'content' => view('translate/page', array('language' => $language, 'key_names' => $key_names))
        ));
    }
}
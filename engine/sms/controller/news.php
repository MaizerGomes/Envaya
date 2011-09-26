<?php

class SMS_Controller_News extends SMS_Controller
{
    static $routes = array(
        array(
            'regex' => '(fn|((f|find)\s+(n|name)))\s+(?P<query>.+)',
            'action' => 'action_find_name',
        ),
        array(
            'regex' => '(f|find)\s+(?P<query>.+)',
            'action' => 'action_find',
        ),
        array(
            'regex' => '((h|help)\s+)?(f|find)\b',
            'action' => 'action_find_help',
        ), 
        array(
            'regex' => '(i|info)\s+(?P<username>\w+)\b',
            'action' => 'action_info',
        ),
        array(
            'regex' => '((h|help)\s+)?(i|info)\b',
            'action' => 'action_info_help',
        ),
        array(
            'regex' => '(n|news)\s+(?P<username>\w+)\b',
            'action' => 'action_news',
        ),
        array(
            'regex' => '((h|help)\s+)?(n|news)\b',
            'action' => 'action_news_help',
        ),    
        array(
            'regex' => '(c|comment)\s+(?P<message>.+)',
            'action' => 'action_add_comment',
        ),
        array(
            'regex' => '(g)\s+(?P<index>\d+)',
            'action' => 'action_view_comment',
        ),
        array(
            'regex' => '(m|more)\b',
            'action' => 'action_more',
        ),
        array(     
            'regex' => '(x|next)\b',
            'action' => 'action_next',
        ),
        array(
            'regex' => '(name)\s+(?P<name>.+)',
            'action' => 'action_set_name',
        ),
        array(
            'regex' => '(name)\b',
            'action' => 'action_get_name',
        ),    
        array(
            'regex' => '(loc|location)\s+(?P<location>.+)',
            'action' => 'action_set_location',
        ),
        array(
            'regex' => '(loc|location)\b',
            'action' => 'action_get_location',
        ),
        array(
            'regex' => '(v|view)\s+(?P<item_id>\d+)\b',
            'action' => 'action_view_id',
        ),
        array(
            'regex' => '(p|post)\s+(?P<message>.+)',
            'action' => 'action_post',
        ),
        array(
            'regex' => '((h|help)\s+)?(p|post)\b',
            'action' => 'action_post_help',
        ),     
        array(
            'regex' => '(delete)\s+(?P<guid>\d+)',
            'action' => 'action_delete',
        ),       
        array(
            'regex' => '((h|help)\s+)?(delete)\b',
            'action' => 'action_delete_help',
        ),
        array(
            'regex' => '(l|lang|language)\s+(?P<lang>\w+)',
            'action' => 'action_language',
        ),        
        array(
            'regex' => '((h|help)\s+)?(l|lang|language)\b',
            'action' => 'action_language_help',
        ),
        array(
            'regex' => '(v)\s+(?P<id>\d+)',
            'action' => 'action_view_item',
        ),                        
        array(
            'regex' => '(in|(log in)|login)\s+(?P<username>[\w\-]+)\s+(?P<password>.*)',
            'action' => 'action_login',
        ),            
        array(
            'regex' => '((h|help)\s+)?(in|(log in)|login)\b',
            'action' => 'action_login_help',
        ),                    
        array(
            'regex' => '(out|(log out)|logout)\b',
            'action' => 'action_logout',
        ),    
        array(
            'regex' => '(h|help|menu)\b',
            'action' => 'action_help',
        ),        
        array(
            'regex' => '(?P<page>\d+)\b',
            'action' => 'action_page',
        ),
        array(
            'regex' => '(x|next)\b',
            'action' => 'action_next',
        ),
        array(
            'regex' => '(?P<message>.*)',
            'action' => 'action_default',
        ),         
    );
    
    function query_paginator($query, $page, $item_fn, $options)
    {
        $page = max($page, 1);
    
        $this->set_state('page', $page);
    
        $count = $query->count();
        
        ob_start();
        if ($count > 0)
        {
            $page_size = @$options['page_size'] ?: 10;
            $num_pages = ceil($count / $page_size);
            
            $offset = ($page-1) * $page_size;
            
            if ($offset >= $count)
            {
                echo @$options['no_more'];
            }
            else
            {                            
                $query->limit($page_size, $offset);
                
                $results = $query->filter();    
                
                echo @$options['header'];
                
                foreach ($results as $result)
                {
                    echo $item_fn($result);
                }
                
                if ($num_pages > 1)
                {
                    echo "[$page/$num_pages]\n";
                }
                echo @$options['footer'];
            }
        }
        else
        {
            echo @$options['empty'];
        }
        return ob_get_clean();
    }
    
    function get_param_or_state($name)
    {
        $val = $this->param($name);
        if ($val)
        {
            $this->set_state("p_$name", $val);
            return $val;
        }
        else
        {
            return $this->get_state("p_$name");
        }        
    }
    
    function view_info($user)
    {
        $this->check_access($user);    
        $this->set_user_context($user);
    
        ob_start();
        echo "{$user->name}\n";
        echo "{$user->city} ".strtoupper($user->country)."\n";
        echo Config::get('domain')."/{$user->username}\n";

        if ($user->email && $user->get_metadata('public_email') != 'no')
        {
            echo "{$user->email}\n";
        }
        
        if ($user->phone_number && $user->get_metadata('public_phone') != 'no')
        {
            echo "{$user->get_primary_phone_number()}\n";
        }            
        echo __('sms:user_news');
        $this->reply(ob_get_clean());
    }
       
    function view_news($user, $page = 1)
    {
        $this->check_access($user);    
        $this->set_user_context($user);
        $this->set_page_action('news');
        
        $news = $user->get_widget_by_class('News');
        if ($news && $news->is_enabled())
        {
            $query = $news->query_published_widgets()
                ->order_by('time_published desc, guid desc');
                
            $query->limit(1);
            
            $self = $this;
            
            $this->reply(
                $this->query_paginator($query, $page, function($post) use ($self) {
                        $text = "";
                        
                        $text .= SMS_Output::short_time($post->time_published)."\n";
                        
                        if ($post->num_comments > 0)
                        {                        
                            $text .= sprintf(__('sms:news_comments'), $post->num_comments)."\n";
                        }
                        else
                        {
                            $text .= __('sms:news_no_comments')."\n";
                        }
                        $text .= SMS_Output::text_from_html($post->content);                       
                        
                        $chunks = SMS_Output::split_text($text, 280);
                        
                        $self->set_state('post_guid', $post->guid);                        
                        $self->set_chunks($chunks);
                        
                        return "{$chunks[0]}\n";
                    }, array(
                    'page_size' => 1,
                    'empty' => sprintf(__('sms:no_news'), $user->username),
                    'no_more' => sprintf(__('sms:no_more_news'), $user->username),
                ))
            );
        }
        else
        {
            $this->reply(__('sms:no_news'));
        }
    } 
              
    function check_access($user)
    {
        if (!$user->can_view())
        {
            throw new NotFoundException(strtr(__('sms:unapproved_user'), array('{username}' => $user->username)));
        }
    }
       
    function lookup_user($username)
    {
        if (!$username)
        {
            throw new NotFoundException();
        }
    
        $user = User::get_by_username($username);
        
        if (!$user)
        {    
            throw new NotFoundException(strtr(__('sms:bad_user'), array('{username}' => $username)).' '.__('sms:find_help'));
        }
        return $user;
    }
       
    function action_info()
    {
        $user = $this->lookup_user($this->param('username'));         
        $this->view_info($user);        
    }
    
    function action_info_help()
    {
        $org = $this->get_user_context();
        if ($org)
        {
            $this->view_info($org);
        }
        else
        {
            ob_start();
            
            echo __('sms:user_help');
            $user = Session::get_loggedin_user();
            if ($user)
            {
                echo "\n".strtr(__('sms:user_self_help'), array('{username}' => $user->username));
            }        
            echo "\n".__('sms:find_help');
            
            $reply = ob_get_clean();
            $this->reply($reply);
        }      
    }
        
    function action_news($page = 1)
    {
        $user = $this->lookup_user($this->param('username') ?: $this->get_state('username'));         
        $this->view_news($user, $page);
    }
    
    function action_news_help()
    {
        $user = $this->get_user_context();
        if ($user)
        {
            $this->view_news($user, $page);
        }
        else
        {
            $this->reply(__('sms:news_help').' '.__('sms:find_help'));
        }
    }    
    
    function action_add_comment()
    {        
        // comment gets added to last post you looked at        
        $post_guid = $this->get_state('post_guid');
        $post = $post_guid ? Widget::get_by_guid($post_guid) : null;
        
        if ($post != null && $this->get_page_action() == 'news')
        {
            $message = $this->param('message');
            
            $comment = new Comment();
            $comment->container_guid = $post->guid;
            $comment->owner_guid = Session::get_loggedin_userid();
            $comment->name = $this->get_state('name');
            $comment->location = $this->get_state('location') ?: '(via sms)';
            $comment->content = $message;
            $comment->save();
        
            $post->refresh_attributes();
            $post->save();
            
            $this->reply(strtr(__('sms:comment_published'), array(
                '{id}' => $comment->guid,
                '{url}' => $post->get_url(),
            )));
        }
        else
        {
            $this->reply(__('sms:no_add_comment_here'));
        }
    }
    
    function view_comment($comment)
    {
        $text = "{$comment->name}\n";
        $text .= "{$comment->location}\n";            
        $text .= SMS_Output::short_time($comment->time_created)."\n";
        $text .= $comment->content;                       
        
        $chunks = SMS_Output::split_text($text, 280);
        
        $this->set_chunks($chunks);
        
        $this->reply($chunks[0]);    
    }
    
    function action_view_comment()
    {
        $post_guid = $this->get_state('post_guid');
        $post = $post_guid ? Widget::get_by_guid($post_guid) : null;
        
        if ($post != null && $this->get_page_action() == 'news')
        {    
            $index = $this->param('index');
            $offset = max(0, (int)$index - 1);
            $comment = $post->query_comments()
                ->limit(1, $offset)
                ->get();
                
            if ($comment)
            {
                $this->view_comment($comment);                
            }
            else
            {
                $this->reply(sprintf(__('sms:comment_not_found'), $index));   
            }
        }
        else
        {
            $this->reply(__('sms:no_view_comment_here'));
        }
    }
    
    function set_chunks($chunks)
    {
        $this->set_state('chunk_index', 0);
        $this->set_state('chunks', (sizeof($chunks) > 1) ? $chunks : null);                    
    }
    
    function get_user_context()
    {
        $username = $this->get_state('username');
        if ($username)
        {   
            return User::get_by_username($username);
        }
        return null;
    }
    
    function set_user_context($user)
    {
        $this->set_state('username', $user ? $user->username : null);
    }
       
    function action_find($page = 1)
    {
        $this->set_page_action('find');
        $this->set_user_context(null);
        
        $q = strtolower($this->get_param_or_state('query') ?: '');
        
        $query = Organization::query()
                ->where_visible_to_user()
                ->order_by('username');
        
        $terms = preg_split('#\s+#', $q);
        
        $loc_terms = array();

        $region = PhoneNumber::get_country_code($this->request->get_from_number());
        if (!Geography::is_available_country($region))
        {
            $country_codes = Config::get('available_countries');
            $region = $country_codes[0];
        }
        
        foreach ($terms as $term)
        {
            if (Geography::is_available_country($term))
            {
                $query->with_country($term);
                $region = $term;
            }
            else
            {
                $loc_terms[] = $term;
            }            
        }
        
        if ($loc_terms)
        {
            $loc_query = implode(' ', $loc_terms);
        
            $latlong = Geography::geocode($loc_query, $region);
            
            $viewport = @$latlong['viewport'];
        
            if ($viewport)
            {        
                $query->in_area(
                    $viewport['southwest']['lat'],
                    $viewport['southwest']['lng'],
                    $viewport['northeast']['lat'],
                    $viewport['northeast']['lng']
                );
            }
            else
            {
                $query->where('0=1');
            }
        }
                
        $this->reply(
            $this->query_paginator($query, $page,
                function($result) {
                    return "{$result->username}\n";
                },
                array(
                    'page_size' => 10,
                    'no_more' => sprintf(__('sms:no_more_orgs'), $q),
                    'empty' => sprintf(__('sms:no_orgs_near'), $q),
                    'footer' => __('sms:user_details'),
                    'header' => '',
                )
            )
        );
    }
    
    function action_find_name($page = 1)
    {
        $this->set_page_action('find_name');
        $this->set_user_context(null);
        
        $q = strtolower($this->get_param_or_state('query') ?: '');

        $query = Organization::query()
                ->where_visible_to_user()
                ->order_by('username');

        $query->fulltext($q);
        
        $this->reply(
            $this->query_paginator($query, $page,
                function($result) {
                    return "{$result->username}\n";
                },
                array(
                    'page_size' => 10,
                    'no_more' => __('sms:no_more_orgs'),
                    'empty' => sprintf(__('sms:no_orgs_name'), $q),
                    'footer' => __('sms:user_details'),
                    'header' => '',
                )
            )
        );        
    }
    
    function action_find_help()
    {
        $this->set_user_context(null);
        $this->reply(__('sms:find_help'));
    }
       
    function action_post()
    {    
        $this->post_message($this->param('message'));
    }
    
    function action_post_help()
    {
        // if user has possible message saved from before, "P" alone will post it
        $message = $this->get_state('message');
        if ($message)
        {
            $this->post_message($message);
        }
        else
        {
            $this->reply(__('sms:post_help'));   
        }        
    }    
        
    function post_message($message)
    {            
        $user = Session::get_loggedin_user();
        if (!$user)
        {
            $this->set_state('message', $message);            
            $this->set_default_action('login');
            $this->reply(__('sms:login_to_post'));      
            return;
        }
        
        $this->set_default_action(null);
        $this->set_user_context(null);
        
        $news = $user->get_widget_by_class('News');
        if (!$news->guid)
        {
            $news->save();
        }
        
        $post = $news->new_widget_by_class('SMSPost');
        $post->owner_guid = $user->guid;
        $post->set_content($message);
        $post->save();
        $post->post_feed_items();
        
        $this->reply(strtr(__('sms:post_published'),
            array(
                '{username}' => $user->username,
                '{url}' => $news->get_url(),
                '{id}' => $post->guid
            )
        ));            
        $this->set_state('message', null);
    }
    
    function action_delete()
    {
        $guid = $this->param('guid');
        
        $item = Entity::get_by_guid($guid);
        
        if (!$item)
        {
            $this->reply(strtr(__('sms:item_not_found'), array('{id}' => $guid)));
        }        
        else if (!$item->can_edit() || !($item instanceof Widget_Post || $item instanceof Comment))
        {
            $this->reply(__('sms:cant_delete_item'));
        }
        else
        {
            $item->disable();
            $item->save();
            
            if ($item instanceof Comment)
            {
                $post = $item->get_container_entity();
                $post->refresh_attributes();
                $post->save();
            }            
            
            $this->reply(__('sms:item_deleted'));
        }
    }    
    
    function action_delete_help()
    {
        $this->reply(__('sms:delete_help'));
    }
    
    function action_language()
    {
        $this->set_user_context(null);    
    
        $lang = strtolower($this->param('lang'));
        
        $languages = Config::get('languages');
        
        if (isset($languages[$lang]))
        {
            $this->set_state('lang', $lang);
            Language::set_current_code($lang);
            $this->reply(__('sms:language_changed'));
        }
        else
        {
            $this->reply(strtr(__('sms:bad_language'), array('lang' => $lang)));
        }
    }
    
    function action_language_help()
    {
        $this->reply(__('sms:language_help'));
    }
    
    function action_logout()
    {        
        $this->set_default_action(null);
        $this->set_user_context(null);    
        $this->set_page_action(null);    
        
        $this->logout();
        $this->reply(__('sms:logout_success'));
    }    
    
    function action_login()
    {
        $this->set_user_context(null);
    
        $username = $this->param('username');
        $password = $this->param('password');
        
        $this->try_login($username, $password);        
    }
    
    function action_login_help()
    {
        $user = Session::get_loggedin_user();
        if (!$user)
        {
            $this->reply(__('sms:login_help'));            
            $this->set_default_action('login');            
        }
        else
        {
            $this->reply(strtr(__('sms:logged_in'), array('{username}' => $user->username, '{name}' => $user->name)));
        }
    }        
        
    function try_login($username, $password)
    {
        $this->set_default_action('login');
        
        $user = User::get_by_username($username);
        
        if (!$user)
        {
            $this->reply(strtr(__('sms:login_unknown_user'), array('{username}' => $username)));
            return;
        }
        else if (!($user instanceof Organization))
        {
            $this->reply(strtr(__('sms:login_not_org'), array('{username}' => $username)));
            return;
        }
        else if (!$user->has_password($password))
        {
            $this->reply(strtr(__('sms:login_bad_password'), array('{username}' => $username, '{password}' => $password)));            
            return;
        }
        else
        {
            $this->set_default_action(null);
            $this->login($user);
            
            $message = $this->get_state('message');
            
            if ($message)
            {
                $this->post_message($message);
            }
            else
            {                        
                $this->reply(__('sms:login_success').' '.__('sms:post_help'));
            }
        }
    }
    
    function set_page_action($page_action)
    {
        $this->set_state('page_action', $page_action);
    }
    
    function get_page_action()
    {
        return $this->get_state('page_action');
    }
    
    function do_page_action($page)
    {
        $page_action = $this->get_page_action();        
        
        if ($page_action)
        {
            $fn = "action_{$page_action}";
            $this->$fn($page);
        }
        else
        {
            throw new NotFoundException();
        }    
    }
    
    function action_page()
    {
        $this->do_page_action((int)$this->param('page'));
    }
    
    function action_more()
    {
        $chunk_index = ($this->get_state('chunk_index') ?: 0) + 1;
    
        $chunks = $this->get_state('chunks');
        
        if ($chunks && $chunk_index < sizeof($chunks))
        {
            $this->reply($chunks[$chunk_index]);
            $this->set_state('chunk_index', $chunk_index);
        }
        else
        {
            $this->reply(__('sms:no_more_content'));
            $this->set_state('chunks', null);
        }           
    }
    
    function action_next()
    {
        $this->do_page_action($this->get_state('page') + 1);
    }
    
    function action_default()
    {
        $message = $this->param('message');
        
        // heuristics to guess possible intent (possibly based on previous state)
        // if the message doesn't match any explicit rules
    
        $default_action = $this->get_default_action();
        
        switch ($default_action)
        {
            case 'login':
                list($username, $password) = explode(" ", $message, 2);
                $this->try_login($username, $password);
                break;
            default:
                if (strlen($message) > 20)
                {    
                    $snippet = substr($message, 0, 20);
                    $this->reply(strtr(__('sms:publish_last_help'), array('snippet' => $snippet)));
                    $this->set_state('message', $message);
                }
                else
                {
                    throw new NotFoundException();
                }
                break;
        }        
    }
    
    function action_help()
    {    
        $this->set_default_action(null);
        $this->reply(__('sms:help'));
    }    
    
    function action_set_name()    
    {   
        $name = $this->param('name');
        $this->set_state('name', $name);
        $this->reply(sprintf(__('sms:name_changed'), $name));
    }
    
    function action_get_name()    
    {   
        $name = $this->get_state('name');
        if ($name)
        {
            $this->reply(sprintf(__('sms:name'), $name).' '.__('sms:name_help'));
        }
        else
        {
            $this->reply(__('sms:name_not_set').' '.__('sms:name_help'));
        }
    }
    
    function action_set_location()    
    {   
        $location = $this->param('location');
        $this->set_state('location', $location);
        $this->reply(sprintf(__('sms:location_changed'), $location));
    }
    
    function action_get_location()    
    {   
        $location = $this->get_state('location');
        if ($location)
        {
            $this->reply(sprintf(__('sms:location'), $location).' '.__('sms:location_help'));
        }
        else
        {
            $this->reply(__('sms:location_not_set').' '.__('sms:location_help'));
        }
    }    
    
    function action_view_item()
    {
        $id = $this->param('id');
        
        $entity = Entity::get_by_guid($id);
        if ($entity)
        {
            if ($entity instanceof Comment)
            {
                $this->view_comment($entity);
            }
            else
            {
                $this->reply(strtr(__('sms:item_not_found'), array('{id}' => $id)));
            }
        }
        else
        {
            $this->reply(strtr(__('sms:item_not_found'), array('{id}' => $id)));
        }
    }
    
    public function execute($message)
    {       
        try
        {
            return parent::execute($message);
        }
        catch (NotFoundException $ex)
        {
            $msg = $ex->getMessage();
            $this->reply($msg ?: __('sms:bad_command'));
            return $this;
        }
    }
}

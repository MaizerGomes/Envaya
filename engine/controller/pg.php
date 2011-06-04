<?php

/*
 * Controller for a wide variety of actions that don't fit anywhere else.
 *
 * URL: /pg/<action>
 */
class Controller_Pg extends Controller 
{
    static $routes; // initialized at bottom of file
    
    function action_login()
    {
        $action = new Action_Login($this);
        $action->execute();   
    }
    
    function action_logout()
    {
        logout();
        $this->redirect('/');
    }    
    
    function action_register()
    {
        $action = new Action_Register($this);
        $action->execute();
    }
    
    function action_dashboard()
    {
        $this->require_login();
        $this->redirect(Session::get_loggedin_user()->get_url()."/dashboard");
    }

    function action_forgot_password()
    {
        $action = new Action_ForgotPassword($this);
        $action->execute();
    }

    function action_password_reset()
    {
        $action = new Action_PasswordReset($this);
        $action->execute();    
    }

    function action_upload()
    {
        $action = new Action_Upload($this);
        $action->execute();
    }

    function action_blank()
    {
        $this->allow_view_types(null);
        // this may be useful for displaying a page containing only SessionMessages
        $this->page_draw(array(
            'no_top_bar' => true, 
            'layout' => 'layouts/frame', 
            'no_top_bar' => true,            
            'content' => SessionMessages::view_all(),
            'header' => ''
        ));
    }

    function action_large_img()
    {
        $owner_guid = get_input('owner');
        $group_name = get_input('group');

        $largeFile = UploadedFile::query()->where('owner_guid = ?', $owner_guid)->where('group_name = ?', $group_name)
            ->order_by('width desc')->get();

        if ($largeFile)
        {
            echo "<html><body><img src='{$largeFile->get_url()}' width='{$largeFile->width}' height='{$largeFile->height}' /></body></html>";
        }
        else
        {
            throw new NotFoundException();
        }
    }

    function action_receive_sms()
    {
        $from = @$_REQUEST['From'];
        $body = @$_REQUEST['Body'];

        error_log("SMS received:\n from=$from body=$body");

        if ($from && $body)
        {
            $sms_request = new SMS_Request($from, $body);
            $sms_request->execute();
        }
        else
        {
            throw new NotFoundException();
        }
    }

    function action_delete_comment()
    {
        $guid = (int)get_input('comment');
        $comment = Comment::get_by_guid($guid);
        if ($comment && $comment->can_edit())
        {
            $comment->disable();
            $comment->save();

            $container = $comment->get_container_entity();
            $container->num_comments = $container->query_comments()->count();
            $container->save();

            SessionMessages::add(__('comment:deleted'));
            
            $this->redirect($container->get_url());
        }
        else
        {
            throw new RedirectException(__('comment:not_deleted'));
        }
    }

    function action_local_store()
    {
        // not for use in production environment
        $storage_local = get_storage();

        if (!($storage_local instanceof Storage_Local))
        {
            throw new NotFoundException();
        }

        $path = get_input('path');

        $components = explode('/', $path);

        foreach ($components as $component)
        {
            if (preg_match('/[^\w\.\-]|(\.\.)/', $component))
            {
                throw new NotFoundException();
            }
        }

        $local_path = $storage_local->get_file_path(implode('/', $components));

        if (!is_file($local_path))
        {
            throw new NotFoundException();
        }

        $mime_type = UploadedFile::get_mime_type($local_path);
        $filename = $components[sizeof($components) - 1];
        
        if ($mime_type && in_array($mime_type, array('text/plain','application/pdf','image/jpeg','image/png','image/gif')))
        {
            // okay to show in browser
        }   
        else
        {
            // possibly dangerous to show in browser; show as download
            $this->request->headers['Content-Disposition'] = "attachment; filename=\"$filename\"";
        }        
        $this->set_content_type($mime_type);
        $this->request->response = file_get_contents($local_path);
    }
    
    function action_hide_todo()
    {
        Session::set('hide_todo', 1);
        
        $this->set_content_type('text/javascript');
        $this->set_response(json_encode("OK"));    
    }
    
    function action_change_lang()
    {
        $url = @$_GET['url'];
        $newLang = $_GET['lang'];
        // $this->change_viewer_language($newLang); // unnecessary because done in default controller
        Session::save_input();
        $this->redirect(url_with_param($url, 'lang', $newLang));
    }
    
    function action_js_revision_content()
    {
        $this->set_content_type('text/javascript');
        
        $id = (int)get_input('id');
        
        $revision = ContentRevision::query()->where('id = ?', $id)->get();
        if (!$revision || !$revision->can_edit())
        {
            throw new SecurityException("Access denied.");
        }
        
        $this->set_response(json_encode(array(
            'content' => $revision->content
        )));
    }
    
    function action_js_revisions()
    {
        $this->set_content_type('text/javascript');
        
        $entity_guid = (int)get_input('entity_guid');
        
        $entity = Entity::get_by_guid($entity_guid, true);
        if (!$entity)
        {
            $revisions = array();
        }
        else
        {
            if (!$entity->can_edit())
            {
                throw new SecurityException("Access denied.");
            }
            
            $revisions = ContentRevision::query()->where('entity_guid = ?', $entity_guid)->order_by('time_updated desc')->filter();        
        }
        
        $this->set_response(json_encode(array(
            'revisions' => array_map(function($r) { return $r->js_properties(); }, $revisions),
        )));
    }

    function action_select_image()
    {
        $file = UploadedFile::get_from_url(get_input('src'));

        $this->allow_view_types(null);
        $this->page_draw(array(
            'layout' => 'layouts/frame',
            'no_top_bar' => true,            
            'content' => view('upload/select_image', array(
                'current' => $file,
                'position' => get_input('pos'),
                'frameId' => get_input('frameId'),
            ))
        ));
    }
    
    function action_select_document()
    {
        $guid = (int)get_input('guid');
        $file = UploadedFile::get_by_guid($guid);
        
        $this->allow_view_types(null);
        $this->page_draw(array(
            'layout' => 'layouts/frame',
            'no_top_bar' => true,
            'content' => view('upload/select_document', array(
                'current' => $file,
                'frameId' => get_input('frameId'),
            ))
        ));        
    }    
    
    function action_confirm_action()
    {
        $action = new Action_ConfirmAction($this);
        $action->execute();
    }
    
    function action_show_captcha()
    {
        Captcha::show();
    }

    function action_email_settings()
    {
        $action = new Action_EmailSettings($this);
        $action->execute();   
    }        

    function action_delete_feed_item()
    {
        $this->validate_security_token();
        $feedItem = FeedItem::query()->where('id = ?', (int)get_input('item'))->get();
        
        if (!$feedItem || !$feedItem->can_edit())
        {
            throw new RedirectException(__('page:notfound:details'));
        }
        
        foreach ($feedItem->query_items_in_group()->filter() as $item)
        {
            $item->delete();
        }           
        SessionMessages::add(__('feed:item_deleted'));
        $this->redirect();
    }   
    
    /*
     * Web entry point for uncompressed CSS files (for testing).     
     */
    function action_css()
    {
        $name = get_input('name');
        
        if (preg_match('/[^\w]/', $name))
        {
            throw new NotFoundException();
        }
        try
        {
            $css = view('css/'.($name ?: 'default'));
        }
        catch (InvalidParameterException $ex)
        {
            throw new NotFoundException();
        }
        
        $this->set_content_type('text/css');        
        $this->set_response($css);
    }
}

Controller_Pg::$routes = Controller::$SIMPLE_ROUTES;
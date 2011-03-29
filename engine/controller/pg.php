<?php

class Controller_Pg extends Controller {

    function action_login()
    {
        $username = get_input('username');
        $next = get_input('next');

        if (Request::is_post())
        {
            $this->submit_login();
        }
        
        $title = __("login");

        $loginTime = (int)get_input('_lt');
        if ($loginTime && time() - $loginTime < 10 && !Session::isloggedin())
        {
            register_error_html(view('account/cookie_error'));
        }

        $body = view_layout('one_column',
            view_title($title, array('org_only' => true)),
            view("account/forms/login", array('username' => $username, 'next' => $next))
        );

        $this->page_draw($title, $body, array('hideLogin' => !Session::isloggedin()));
    }

    private function submit_login()
    {
        $username = get_input('username');
        $password = get_input("password");
        $next = get_input('next');
        $persistent = get_input("persistent", false);

        $result = false;
        if (!empty($username) && !empty($password))
        {
            if ($user = authenticate($username,$password))
            {
                $result = login($user, $persistent);
            }
        }

        if ($result)
        {
            system_message(sprintf(__('loginok'), $user->name));

            if (!$next)
            {
                if (!$user->is_setup_complete())
                {
                    $next = "org/new?step={$user->setup_state}";
                }
                else
                {
                    $next = "{$user->get_url()}/dashboard";
                }
            }

            $next = url_with_param($next, '_lt', time());

            forward($next);
        }
        else
        {
            return register_error_html(view('account/login_error'));
        }
    }    
    
    function action_tci_donate_frame()
    {
        echo view("page/tci_donate_frame", $values);
    }

    function action_submit_donate_form()
    {
        $values = $_POST;
        $amount = (int)$values['_amount'] ?: (int)$values['_other_amount'];
        $values['donation'] = $amount;

        $emailBody = "";

        foreach ($values as $k => $v)
        {
            $emailBody .= "$k = $v\n\n";
        }

        send_admin_mail(Zend::mail("Donation form started", $emailBody));

        if (!$amount)
        {
            action_error("Please select a donation amount.");
        }
        if (!$values['Name'])
        {
            action_error("Please enter your Full Name.");
        }
        if (!$values['phone'])
        {
            action_error("Please enter your Phone Number.");
        }
        if (!$values['Email'])
        {
            action_error("Please enter your Email Address.");
        }

        unset($values['_amount']);
        unset($values['_other_amount']);
        unset($values['Submit']);

        echo view("page/submit_tci_donate_form", $values);
    }

    function action_logout()
    {
        logout();
        forward();
    }

    function action_dashboard()
    {
        $this->require_login();
        forward(Session::get_loggedin_user()->get_url()."/dashboard");
    }

    function action_forgot_password()
    {
        $body = view("account/forms/forgotten_password",
            array('username' => get_input('username'))
        );

        $title = __('user:password:reset');
        $this->page_draw($title, view_layout("one_column",
            view_title($title, array('org_only' => true)), $body));
    }

    function action_request_new_password()
    {
        $username = get_input('username');

        $user = get_user_by_username($username);
        if (!$user)
        {
            $user = User::query(true)->where('email = ?', $username)->get();
        }

        if ($user)
        {
            if (!$user->email)
            {
                register_error(__('user:password:resetreq:no_email'));
                forward("page/contact");
            }

            $user->passwd_conf_code = substr(generate_random_cleartext_password(), 0, 24); // avoid making url too long for 1 line in email
            $user->save();

            $mail = Zend::mail(
                __('email:resetreq:subject',$user->language),
                view('emails/password_reset_request', array('user' => $user))
            );
            
            if ($user->send_mail($mail))
            {
                system_message(__('user:password:resetreq:success'));
            }
            else
            {
                register_error(__('user:password:resetreq:fail'));
            }
        }
        else
        {
            action_error(sprintf(__('user:username:notfound'), $username));
        }

        forward();
    }

    function action_password_reset()
    {
        $this->require_https();

        $user_guid = get_input('u');
        $conf_code = get_input('c');

        $user = get_user($user_guid);

        if ($user && $user->passwd_conf_code && $user->passwd_conf_code == $conf_code)
        {
            $title = __("user:password:choose_new");
            $body = view_layout('one_column_padded',
                view_title($title, array('org_only' => true)),
                view("account/forms/reset_password", array('entity' => $user)));
            $this->page_draw($title, $body);
        }
        else
        {
            register_error(__('user:password:fail'));
            forward("pg/login");
        }
    }

    function action_submit_password_reset()
    {
        $user_guid = get_input('u');
        $conf_code = get_input('c');
        $user = get_user($user_guid);

        if ($user && $user->passwd_conf_code && $user->passwd_conf_code == $conf_code)
        {
            $password = get_input('password');
            $password2 = get_input('password2');
            if ($password!="")
            {
                try
                {
                    validate_password($password);
                }
                catch (RegistrationException $ex)
                {
                    action_error($ex->getMessage());
                }

                if ($password == $password2)
                {
                    $user->set_password($password);
                    $user->passwd_conf_code = null;
                    $user->save();
                    system_message(__('user:password:success'));
                    login($user);
                    forward("pg/dashboard");
                }
                else
                {
                    action_error(__('user:password:fail:notsame'));
                }
            }
        }
        else
        {
            register_error(__('user:password:fail'));
            forward("pg/login");
        }
    }


    function action_register()
    {
        $friend_guid = (int) get_input('friend_guid',0);
        $invitecode = get_input('invitecode');

        if (!Session::isloggedin())
        {
            $body = view_layout('one_column_padded', view_title(__("register")), view("account/forms/register", array('friend_guid' => $friend_guid, 'invitecode' => $invitecode)));
            $this->page_draw(__('register'), $body);
        }
        else
        {
            forward();
        }
    }

    function action_submit_registration()
    {
        $username = get_input('username');
        $password = get_input('password');
        $password2 = get_input('password2');
        $email = get_input('email');
        $name = get_input('name');

        if ($password != $password2)
        {
            action_error(__('create:passwords_differ'));
        }

        try
        {
            $new_user = register_user($username, $password, $name, $email);
            login($new_user, false);
            system_message(__("registerok"));
            forward("pg/dashboard/");
        }
        catch (RegistrationException $r)
        {
            action_error($r->getMessage());
        }
    }

    function action_upload_frame()
    {
        $this->request->response = view('upload_frame');
    }

    private function upload_file_in_mode($file_input, $mode)
    {   
        if (!$file_input || $file_input['error'] != 0)
        {    
            $error_code = $file_input ? get_constant_name($file_input['error'], 'UPLOAD_ERR') : 'UPLOAD_ERR_NO_FILE';
            throw new IOException(sprintf(__("upload:transfer_error"), $error_code));
        }    
    
        switch ($mode)
        {
            case 'image':        
                $sizes = json_decode(get_input('sizes'));
                return UploadedFile::upload_images_from_input($file_input, $sizes);           
            case 'scribd':
                return UploadedFile::upload_scribd_from_input($file_input);
            default:
                return UploadedFile::upload_from_input($file_input);
        }         
    }
    
    function action_upload()
    {
        $this->require_login();
        
        try
        {  
            $files = $this->upload_file_in_mode($_FILES['file'], get_input('mode'));        
            $json = UploadedFile::json_encode_array($files);            
        }
        catch (Exception $ex)
        {
            $json = json_encode(array('error' => $ex->getMessage()));
        }
                
        if (get_input('iframe'))
        {
            Session::set('lastUpload', $json);
            forward("pg/upload_frame?".http_build_query($_POST));
        }
        else
        {
            header("Content-Type: text/javascript");
            echo $json;
            exit();
        }
    }

    function action_send_feedback()
    {
        $message = get_input('message');
        $from = get_input('name');
        $email = get_input('email');
        
        if (!$message)
        {
            action_error(__('feedback:empty'));
        }
        
        if (!$email)
        {
            action_error(__('feedback:email_empty'));
        }

        try
        {
            validate_email_address($email);
        }
        catch (RegistrationException $ex)
        {
            action_error($ex->getMessage());
        }
        
        $mail = Zend::mail("User feedback", "From: $from\n\nEmail: $email\n\n$message");
        $mail->setReplyTo($email);
        
        send_admin_mail($mail);
        system_message(__('feedback:sent'));
        forward("/");
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
            not_found();
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
            not_found();
        }
    }

    function action_delete_comment()
    {
        $guid = (int)get_input('comment');
        $comment = Comment::query()->where('e.guid=?', $guid)->get();
        if ($comment && $comment->can_edit())
        {
            $comment->disable();
            $comment->save();

            $container = $comment->get_container_entity();
            $container->num_comments = $container->query_comments()->count();
            $container->save();

            system_message(__('comment:deleted'));
        }
        else
        {
            register_error(__('comment:not_deleted'));
        }
        forward_to_referrer();
    }

    function action_local_store()
    {
        // not for use in production environment
        $storage_local = get_storage();

        if (!($storage_local instanceof Storage_Local))
        {
            return not_found();
        }

        $path = get_input('path');

        $components = explode('/', $path);

        foreach ($components as $component)
        {
            if (preg_match('/[^\w\.\-]|(\.\.)/', $component))
            {
                return not_found();
            }
        }

        $local_path = $storage_local->get_file_path(implode('/', $components));

        if (!is_file($local_path))
        {
            return not_found();
        }

        $mime_type = UploadedFile::get_mime_type($local_path);
        if ($mime_type)
        {
            header("Content-Type: $mime_type");
        }
        echo file_get_contents($local_path);
        exit;
    }
    
    function action_hide_todo()
    {
        Session::set('hide_todo', 1);
        
        $this->request->headers['Content-Type'] = 'text/javascript';
        $this->request->response = json_encode("OK");    
    }
    
    function action_change_lang()
    {
        $url = @$_GET['url'];
        $newLang = $_GET['lang'];
        // change_viewer_language($newLang); // unnecessary because done in start.php
        Session::save_input();
        forward(url_with_param($url, 'lang', $newLang));
    }
    
    function action_js_revision_content()
    {
        $this->request->headers['Content-Type'] = 'text/javascript';                
        
        $id = (int)get_input('id');
        
        $revision = ContentRevision::query()->where('id = ?', $id)->get();
        if (!$revision || !$revision->can_edit())
        {
            throw new SecurityException("Access denied.");
        }
        
        $this->request->response = json_encode(array(
            'content' => $revision->content
        ));
    }
    
    function action_js_revisions()
    {
        $this->request->headers['Content-Type'] = 'text/javascript';                
        
        $entity_guid = (int)get_input('entity_guid');
        
        $entity = get_entity($entity_guid, true);
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
        
        $this->request->response = json_encode(array(
            'revisions' => array_map(function($r) { return $r->js_properties(); }, $revisions),
        ));
    }
}
<?php

class Controller_Profile extends Controller
{
    protected $org;
    protected $user;

    function before()
    {
        $user = get_user_by_username($this->request->param('username'));
        if ($user)
        {
            $this->user = $user;

            if ($user instanceof Organization)
            {
                $this->org = $user;
            }
            set_page_owner($user->guid);
        }
        else
        {
            not_found();
        }
    }

    function action_index()
    {
        $widgetName = $this->request->param('widgetname');

        if ($this->org && in_array($widgetName, Widget::getAvailableNames()))
        {
            $widget = $this->org->getWidgetByName($widgetName);
            return $this->index_widget($widget);
        }

        if (!$this->org && $widgetName == 'home')
        {
            $widgetName = 'settings';
        }

        $methodName = "index_$widgetName";
        if (method_exists($this,$methodName))
        {
            return $this->$methodName();
        }
        else
        {
            not_found();
        }
    }

    function action_save()
    {
        action_gatekeeper();

        if (!$this->user->canEdit())
        {
            action_error(elgg_echo('org:cantedit'));
        }

        $widgetName = $this->request->param('widgetname');

        if ($this->org && in_array($widgetName, Widget::getAvailableNames()))
        {
            $widget = $this->org->getWidgetByName($widgetName);
            $this->save_widget($widget);
        }

        $methodName = "save_$widgetName";
        if (method_exists($this,$methodName))
        {
            $this->$methodName();
        }
        else
        {
            not_found();
        }

        forward(get_input('from') ?: $this->user->getURL());
    }

    function action_edit()
    {
        $this->require_editor();
        $this->require_org();

        $org = $this->org;
        $widgetName = $this->request->param('widgetname');

        if (!in_array($widgetName, Widget::getAvailableNames()))
        {
            not_found();
        }
        else
        {
            $widget = $org->getWidgetByName($widgetName);

            $widgetTitle = elgg_echo("widget:{$widget->widget_name}");

            if ($widget->guid && $widget->isEnabled())
            {
                $title = sprintf(elgg_echo("widget:edittitle"), $widgetTitle);
            }
            else
            {
                $title = sprintf(elgg_echo("widget:edittitle:new"), $widgetTitle);
            }

            $cancelUrl = get_input('from') ?: $widget->getUrl();

            add_submenu_item(elgg_echo("canceledit"), $cancelUrl, 'edit');

            $body = elgg_view_layout('one_column',
                elgg_view_title($title), $widget->renderEdit());

            $this->page_draw($title, $body);
        }
    }

    function use_public_layout()
    {
        $org = $this->org;
        global $CONFIG;
        $CONFIG->sitename = $org->name;

        set_theme(get_input("__theme") ?: $org->theme ?: 'green');
        add_org_menu($org);
        set_context('orgprofile');
    }

    function use_editor_layout()
    {
        set_theme('editor');
        set_context('editor');
    }

    function require_editor()
    {
        gatekeeper();

        $user = $this->user;

        if ($user && $user->canEdit())
        {
            $this->use_editor_layout();

            return;
        }
        else if ($user)
        {
            force_login();
        }
        else
        {
            not_found();
        }
    }

    function require_org()
    {
        if (!$this->org)
        {
            not_found();
        }
    }

    function index_design()
    {
        $this->require_editor();
        $org = $this->org;

        $cancelUrl = get_input('from') ?: $org->getUrl();

        add_submenu_item(elgg_echo("canceledit"), $cancelUrl, 'edit');

        $title = elgg_echo("design:edit");
        $area1 = elgg_view("org/design", array('entity' => $org));
        $body = elgg_view_layout("one_column", elgg_view_title($title), $area1);

        $this->page_draw($title,$body);
    }

    function save_design()
    {
        $this->require_org();
        $org = $this->org;

        $theme = get_input('theme');

        if ($theme != $org->theme)
        {
            system_message(elgg_echo("theme:changed"));
            $org->theme = $theme;
            $org->save();
        }

        $iconFiles = get_uploaded_files($_POST['icon']);

        if (get_input('deleteicon'))
        {
            $org->setIcon(null);
            system_message(elgg_echo("icon:reset"));
        }
        else if ($iconFiles)
        {
            $org->setIcon($iconFiles);
            system_message(elgg_echo("icon:saved"));
        }

        $headerFiles = get_uploaded_files($_POST['header']);

        $customHeader = (int)get_input('custom_header');

        if (!$customHeader)
        {
            if ($org->custom_header)
            {
                $org->setHeader(null);
                system_message(elgg_echo("header:reset"));
            }
        }
        else if ($headerFiles)
        {
            $org->setHeader($headerFiles);
            system_message(elgg_echo("header:saved"));
        }
    }

    function index_widget($widget)
    {
        $org = $this->org;

        $this->use_public_layout();

        $viewOrg = $org->canView();

        if ($widget && $widget->widget_name == 'home')
        {
            $subtitle = $org->getLocationText(false);
            $title = '';
        }
        else if (!$widget || !$widget->isActive())
        {
            org_page_not_found($org);
        }
        else
        {
            $subtitle = elgg_echo("widget:{$widget->widget_name}");
            $title = $subtitle;
        }

        if ($org->canEdit())
        {
            add_submenu_item(elgg_echo("widget:edit"), $widget->getEditURL(), 'edit');
        }

        if (get_input("__topbar") != "0")
        {
            $org->showCantViewMessage();

            $preBody = '';

            if (isadminloggedin())
            {
                $preBody .= elgg_view("org/admin_box", array('entity' => $org));
            }

            if ($org->canCommunicateWith())
            {
                $preBody .= elgg_view("org/comm_box", array('entity' => $org));
            }

            if ($org->guid == get_loggedin_userid() && $org->approval == 0)
            {
                $preBody .= elgg_view("org/setupNextStep");
            }
        }

        if ($viewOrg)
        {
            $body = org_view_body($org, $subtitle, ($viewOrg ? $widget->renderView() : ''), $preBody);
        }
        else
        {
            $body = '';
        }

        $this->page_draw($title, $body);
    }

    function save_widget($widget)
    {
        if (get_input('delete'))
        {
            $widget->disable();
            $widget->save();

            system_message(elgg_echo('widget:delete:success'));

            forward($this->user->getURL());
        }
        else
        {
            if (!$widget->isEnabled())
            {
                $widget->enable();
            }

            try
            {
                $widget->saveInput();
                system_message(elgg_echo('widget:save:success'));
                forward($widget->getURL());
            }
            catch (Exception $ex)
            {
                action_error($ex->getMessage());
            }
        }
    }

    function index_help()
    {
        $this->require_editor();
        $this->require_org();

        $title = elgg_echo("help:title");
        $area = elgg_view("org/help", array('org' => $this->org));
        $body = elgg_view_layout('one_column_padded', elgg_view_title($title), $area);
        $this->page_draw($title, $body);
    }

    function index_dashboard()
    {
        $this->require_editor();
        $this->require_org();

        $org = $this->org;
        if ($org->guid == get_loggedin_userid())
        {
            $title = elgg_echo("dashboard");
        }
        else
        {
            $title = sprintf(elgg_echo("dashboard:other_user"), $org->name);
        }

        $area1 = elgg_view("org/dashboard", array('org' => $org));
        $body = elgg_view_layout("one_column", elgg_view_title($title), $area1);

        $this->page_draw($title,$body);
    }

    function index_username()
    {
        $this->require_editor();
        $this->require_org();

        admin_gatekeeper();

        $title = elgg_echo('username:title');
        $area1 = elgg_view('org/changeUsername', array('org' => $this->org));
        $body = elgg_view_layout("one_column", elgg_view_title($title), $area1);

        $this->page_draw($title,$body);
    }

    function save_username()
    {
        $this->require_org();
        $org = $this->org;

        $username = get_input('username');

        $oldUsername = $org->username;

        if ($username && $username != $oldUsername)
        {
            try
            {
                validate_username($username);
            }
            catch (RegistrationException $ex)
            {
                register_error($ex->getMessage());
                forward_to_referrer();
            }

            if (get_user_by_username($username))
            {
                register_error(elgg_echo('registration:userexists'));
                forward_to_referrer();
            }

            $org->username = $username;
            $org->save();

            get_cache()->delete(get_cache_key_for_username($oldUsername));

            system_message(elgg_echo('username:changed'));
        }
        forward($org->getURL());
    }

    function index_feed()
    {
        $this->require_editor();
        $this->require_org();

        $title = elgg_echo("feed:org");

        page_set_translatable(false);

        $area = elgg_view('org/orgfeed', array('org' => $this->org));

        $body = elgg_view_layout('one_column', elgg_view_title($title), $area);

        $this->page_draw($title, $body);
    }

    function index_compose()
    {
        gatekeeper();
        $this->use_editor_layout();
        $this->require_org();

        $org = $this->org;

        if (!get_loggedin_user()->isApproved())
        {
            register_error(elgg_echo('message:needapproval'));
            forward_to_referrer();
        }

        add_submenu_item(elgg_echo("message:cancel"), $org->getURL(), 'edit');

        $title = elgg_echo("message:title");
        $area1 = elgg_view("org/composeMessage", array('entity' => $org));
        $body = elgg_view_layout("one_column", elgg_view_title($title), $area1);
        $this->page_draw($title,$body);
    }

    function index_settings()
    {
        $this->require_editor();

        $title = elgg_echo("usersettings:user");

        $body = elgg_view_layout("one_column", elgg_view_title($title),
            elgg_view("usersettings/form", array('entity' => $this->user)));

        return $this->page_draw($title, $body);
    }

    function save_settings()
    {
        $user = $this->user;

        $name = get_input('name');

        if ($name)
        {
            if (strcmp($name, $user->name)!=0)
            {
                $user->name = $name;
                system_message(elgg_echo('user:name:success'));
            }
        }
        else
        {
            action_error(elgg_echo('create:no_name'));
        }

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
                $user->salt = generate_random_cleartext_password(); // Reset the salt
                $user->password = generate_user_password($user, $password);
                system_message(elgg_echo('user:password:success'));
            }
            else
            {
                action_error(elgg_echo('user:password:fail:notsame'));
            }
        }

        $language = get_input('language');
        if ($language && $language != $user->language)
        {
            $user->language = $language;
            change_viewer_language($user->language);
            system_message(elgg_echo('user:language:success'));
        }

        $email = trim(get_input('email'));
        if ($email != $user->email)
        {
            try
            {
                validate_email_address($email);
            }
            catch (RegistrationException $ex)
            {
                action_error($ex->getMessage());
            }

            $user->email = $email;
            system_message(elgg_echo('user:email:success'));
        }

        $phone = get_input('phone');
        if ($phone != $user->phone_number)
        {
            $user->phone_number = $phone;
            system_message(elgg_echo('user:phone:success'));
        }

        if ($user instanceof Organization)
        {
            $notify_days = get_input('notify_days');
            if ($notify_days != $user->notify_days)
            {
                $user->notify_days = $notify_days;
                system_message(elgg_echo('user:notification:success'));
            }
        }

        $user->save();
    }

    function index_confirm_partner()
    {
        $this->require_org();
        gatekeeper();

        $partner = $this->org;

        $org = get_loggedin_user();
        if (!$org instanceof Organization)
        {
            not_found();
        }

        if ($partner)
        {
            $partnership = $org->getPartnership($partner);
            if ($partnership->isSelfApproved() || !$partnership->isPartnerApproved())
            {
                not_found();
            }

            $title = elgg_echo("partner:confirm");
            $area1 = elgg_view("org/confirmPartner", array('entity' => $org, 'partner' => $partner));
            $body = elgg_view_layout("one_column", elgg_view_title($title), $area1);
            $this->page_draw($title,$body);
        }
        else
        {
            not_found();
        }
    }

    function index_request_partner()
    {
        $this->require_org();
        gatekeeper();
        action_gatekeeper();

        global $CONFIG;

        $partner = $this->org;

        $loggedInOrg = get_loggedin_user();

        if (!$loggedInOrg->isApproved())
        {
            action_error(elgg_echo('partner:needapproval'));
        }

        if (!$partner || $partner_guid == $loggedInOrg->guid)
        {
            register_error(elgg_echo("partner:invalid"));
            forward();
        }
        else
        {
            $partnership = $loggedInOrg->getPartnership($partner);
            $partnership->setSelfApproved(true);
            $partnership->save();

            $partnership2 = $partner->getPartnership($loggedInOrg);
            $partnership2->setPartnerApproved(true);
            $partnership2->save();

            notify_user($partner->guid, $CONFIG->site_guid,
                sprintf(elgg_echo('email:requestPartnership:subject',$partner->language), $loggedInOrg->name, $partner->name),
                sprintf(elgg_echo('email:requestPartnership:body',$partner->language), $partnership->getApproveUrl()),
                NULL, 'email');

            system_message(elgg_echo("partner:request_sent"));

            forward($partner->getUrl());
        }
    }

    function index_create_partner()
    {
        $this->require_org();
        gatekeeper();
        action_gatekeeper();

        $user = get_loggedin_user();

        $partner = $this->org;

        if (!$partner || $partner_guid == $user->guid)
        {
            register_error(elgg_echo("partner:invalid"));
            forward();
        }
        else
        {
            $partnership = $partner->getPartnership($user);
            $partnership->setPartnerApproved(true);
            $partnership->save();

            $partnership2 = $user->getPartnership($partner);
            $partnership2->setSelfApproved(true);
            $partnership2->save();

            $partWidget = $user->getWidgetByName('partnerships');
            $partWidget->save();

            $partWidget2 = $partner->getWidgetByName('partnerships');
            $partWidget2->save();

            system_message(elgg_echo("partner:created"));

            post_feed_items($user, 'partnership', $partner);

            notify_user($partner->guid, null,
                sprintf(elgg_echo('email:partnershipConfirmed:subject',$partner->language), $user->name, $partner->name),
                sprintf(elgg_echo('email:partnershipConfirmed:body',$partner->language), $partWidget2->getURL()),
                NULL, 'email');

            forward($partWidget->getURL());
        }
    }

    function index_send_message()
    {
        $this->require_org();
        gatekeeper();
        action_gatekeeper();

        $user = get_loggedin_user();

        $recipient = $this->org;

        if (!$recipient || !$user->isApproved())
        {
            register_error(elgg_echo("message:invalid_recipient"));
            forward();
        }
        else
        {
            $subject = get_input('subject');
            if (!$subject)
            {
                action_error(elgg_echo("message:subject_missing"));
            }

            $message = get_input('message');
            if (!$message)
            {
                action_error(elgg_echo("message:message_missing"));
            }

            $headers = array(
                'To' => $recipient->getNameForEmail(),
                'From' => $user->getNameForEmail(),
                'Reply-To' => $user->getNameForEmail(),
                'Bcc' => $user->getNameForEmail(),
            );

            send_mail($recipient->email, $subject, $message, $headers);

            system_message(elgg_echo("message:sent"));

            forward($recipient->getURL());
        }
    }
}
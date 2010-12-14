<?php

class Controller_Org extends Controller
{
    function before()
    {
        $this->add_generic_footer();
    }

    function action_browse()
    {
        $this->require_http();
    
        $title = __("browse:title");
        $sector = get_input('sector');
        
        $list = get_input("list");
        
        if ($list || get_viewtype() == 'mobile')
        {
            $area = view("org/browseList", array('lat' => $lat, 'long' => $long, 'sector' => $sector));
        }
        else
        {
            $lat = get_input('lat');
            $long = get_input('long');
            $zoom = get_input('zoom');

            $area = view("org/browseMap", array('lat' => $lat, 'long' => $long, 'zoom' => $zoom, 'sector' => $sector));        
        }
        

        $body = view_layout('one_column', view_title($title), $area);

        $this->page_draw($title, $body);
    }
    
    function action_search()
    {
        $this->require_http();
    
        $query = get_input('q');
        $sector = get_input('sector');
        
        $vars = array('query' => $query, 'sector' => $sector);
        
        if (empty($query) && !$sector)
        {        
            $title = __('search:title');
            $content = view('org/search', $vars);
        }
        else
        {
            $title = __('search:results');            
            
            if (!empty($query)) 
            {
                $geoQuery = "$query Tanzania";            
                $latlong = Geocoder::geocode($geoQuery);
            }
            
            if ($latlong)
            {
                $vars['nearby'] = Organization::query_by_area(
                    array(
                        $latlong['lat'] - 1.0, 
                        $latlong['long'] - 1.0, 
                        $latlong['lat'] + 1.0, 
                        $latlong['long'] + 1.0
                    ),    
                    $sector)->limit(1)->get() != null;
                $vars['latlong'] = $latlong;
            }            

            $vars['results'] = Organization::list_search($query, $sector, $region=null, $limit = 10);            
            
            $content = view('org/search_results', $vars);
        }

        $body = view_layout('one_column', view_title($title), $content);
        $this->page_draw($title,$body);
    }

    function action_feed()
    {
        $this->require_http();
    
        $title = __("feed:title");
        
        $sector = get_input('sector');
        $region = get_input('region');
        $feedName = get_feed_name(array('sector' => $sector, 'region' => $region));
        $items = FeedItem::query_by_feed_name($feedName)->limit(20)->filter();

        $area = view("org/feed", array('sector' => $sector, 'region' => $region, 'items' => $items));

        PageContext::set_rss(true);
        PageContext::set_translatable(false);

        $body = view_layout('one_column', view_title($title), $area);

        $this->page_draw($title, $body);
    }

    function action_new()
    {
        $step = ((int) get_input('step')) ?: 1;
        
        if ($step > 3)
        {
            $step = 1;
        }

        $loggedInUser = Session::get_loggedin_user();

        if ($loggedInUser && !($loggedInUser instanceof Organization))
        {
            logout();
            forward("org/new");
        }

        if ($step == 3 && !$loggedInUser)
        {
            register_error(__("create:notloggedin"));
            $step = 1;
            forward('pg/login');
        }

        if ($loggedInUser  && $step < 3)
        {
            $step = 3;
        }

        if ($step == 2 && !Session::get('registration'))
        {
            register_error(__("qualify:missing"));
            $step = 1;
        }

        $title = __("register:title");
        $body = view_layout('one_column', view_title($title, array('org_only' => true)), view("org/register$step"));
        $this->page_draw($title, $body);
    }

    function action_register1()
    {
        $approvedCountries = array('tz');

        try
        {
            $country = get_input('country');

            if (!in_array($country, $approvedCountries))
            {
                throw new RegistrationException(__("qualify:wrong_country"));
            }

            $orgType = get_input('org_type');
            if ($orgType != 'np')
            {
                throw new RegistrationException(__("qualify:wrong_org_type"));
            }

            Session::set('registration', array(
                //'registration_number' => get_input('registration_number'),
                'country' => get_input('country'),
            ));

            system_message(__("qualify:ok"));
            global $CONFIG;
            forward("{$CONFIG->secure_url}org/new?step=2");            

        }
        catch (RegistrationException $r)
        {
            action_error($r->getMessage());
        }
    }
    
    function action_register2()
    {
        $this->validate_security_token();

        try
        {           
            $org = $this->process_create_account_form();
            
            $prevInfo = Session::get('registration');            
            Session::set('registration', null);

            $org->country = $prevInfo['country'];
            $org->save();

            global $CONFIG;
            forward("{$CONFIG->secure_url}org/new?step=3");
        }
        catch (RegistrationException $r)
        {
            action_error($r->getMessage());
        }
    }

    function action_register3()
    {
        $this->require_login();
        $this->validate_security_token();

        try
        {
            $org = $this->process_create_profile_form();
            forward($org->get_url());
        }
        catch (RegistrationException $r)
        {
            action_error($r->getMessage());
        }
    }

    function action_searchArea()
    {
        $latMin = get_input('latMin');
        $latMax = get_input('latMax');
        $longMin = get_input('longMin');
        $longMax = get_input('longMax');
        $sector = get_input('sector');

        $orgs = Organization::query_by_area(array($latMin, $longMin, $latMax, $longMax), $sector)->filter();

        $orgJs = array();

        foreach ($orgs as $org)
        {
            $orgJs[] = $org->js_properties();
        }

        $this->request->headers['Content-Type'] = 'text/javascript';
        $this->request->response = json_encode($orgJs);
    }

    function action_emailSettings()
    {
        $email = get_input('e');
        $code = get_input('c');
        $users = User::query(true)->where('email = ?', $email)->filter();

        $title = __("user:notification:label");

        if ($email && $code == get_email_fingerprint($email) && sizeof($users) > 0)
        {
            $area1 = view('org/emailSettings', array('email' => $email, 'users' => $users));
        }
        else
        {
            $area1 = __("user:notification:invalid");
        }
        $body = view_layout("one_column_padded", view_title($title), $area1);

        $this->page_draw($title, $body);
    }

    function action_emailSettings_save()
    {
        $email = get_input('email');
        $code = get_input('code');
        $notifications = get_bit_field_from_options(get_input_array('notifications'));
        $users = User::query(true)->where('email = ?', $email)->filter();

        foreach ($users as $user)
        {
            $user->notifications = $notifications;
            $user->save();

            system_message(__('user:notification:success'));
        }

        forward("/");
    }

    function action_selectImage()
    {
        $this->page_draw_vars['no_top_bar'] = true;

        $file = get_file_from_url(get_input('src'));

        $content = view('org/selectImage',
            array(
                'current' => $file,
                'position' => get_input('pos'),
                'frameId' => get_input('frameId'),
            )
        );
        $this->page_draw('',$content);
    }

    function action_translate()
    {
        $this->require_admin();
        PageContext::set_theme('editor');

        $props = get_input_array("prop");
        $from = get_input('from');
        
        $targetLang = get_input('targetlang') ?: get_language();

        $area2 = array();

        foreach ($props as $propStr)
        {
            $guidProp = explode('.', $propStr);
            $guid = $guidProp[0];
            $prop = $guidProp[1];
            $isHTML = (int)$guidProp[2];

            $entity = get_entity($guid);

            if ($entity && $entity->can_edit() && $entity->get($prop))
            {
                $area2[] = view("translation/translate",
                    array(
                        'entity' => $entity,
                        'property' => $prop,
                        'targetLang' => $targetLang,
                        'isHTML' => $isHTML,
                        'from' => $from));
            }
        }

        $title = __("trans:translate");

        $body = view_layout("one_column_wide", view_title($title), implode("<hr><br>", $area2));

        $this->page_draw($title,$body);
    }

    function action_save_translation()
    {
        $this->require_login();
        $this->validate_security_token();

        $text = get_input('translation');
        $guid = get_input('entity_guid');
        $isHTML = (int)get_input('html');
        $property = get_input('property');
        $entity = get_entity($guid);

        if (!$entity->can_edit())
        {
            register_error(__("org:cantedit"));
            forward_to_referrer();
        }
        else
        {
            $origLang = $entity->get_language();

            $actualOrigLang = get_input('language');
            $newLang = get_input('newLang');

            if ($actualOrigLang != $origLang)
            {
                $entity->language = $actualOrigLang;
                $entity->save();
            }
            if ($actualOrigLang != $newLang)
            {
                $trans = $entity->lookup_translation($property, $actualOrigLang, $newLang, TranslateMode::ManualOnly, $isHTML);
                
                if (get_input('delete'))
                {
                    $trans->delete();
                }
                else
                {                
                    $trans->html = $isHTML;
                    $trans->owner_guid = Session::get_loggedin_userid();
                    $trans->value = $text;
                    $trans->save();
                }
            }

            system_message(__("trans:posted"));

            forward(get_input('from') ?: $entity->get_url());
        }
    }

    function action_translate_interface()
    {
        $this->require_login();

        if (get_input('exception'))
        {
            throw new Exception("test exception!");
        }

        $lang = 'sw';

        load_translation($lang);

        $key = get_input('key');

        if ($key)
        {
            $title = __("trans:item_title");
            $body = view_layout("one_column_padded", view_title($title), view("translation/interface_item", array('lang' => $lang, 'key' => $key)));
            $this->page_draw($title, $body);
        }
        else if (get_input('export'))
        {
            header("Content-type: text/plain");
            echo view("translation/interface_export", array('lang' => $lang));
        }
        else
        {
            $title = __("trans:list_title");
            $body = view_layout("one_column_wide", view_title($title), view("translation/interface_list", array('lang' => $lang)));
            $this->page_draw($title, $body);
        }
    }

    function action_save_interface_item()
    {
        $this->require_login();
        $this->validate_security_token();

        $key = get_input('key');
        $value = get_input('value');
        $lang = 'sw';

        $trans = InterfaceTranslation::getByKeyAndLang($key, $lang);

        if (!$trans)
        {
            $trans = new InterfaceTranslation();
            $trans->key = $key;
            $trans->lang = $lang;
        }

        $trans->approval = 0;
        $trans->owner_guid = Session::get_loggedin_userid();
        $trans->value = $value;
        $trans->save();

        system_message(__("trans:posted"));

        forward(get_input('from'));
    }
    
    function action_featured()
    {
        PageContext::set_translatable(false);
        $title = __('featured:title');
        $body = view('org/featured');
        $this->page_draw($title, view_layout("one_column", view_title($title), $body));
    }
}
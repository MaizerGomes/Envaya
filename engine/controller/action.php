<?php

class Controller_Action extends Controller
{
    function action_save_settings()
    {
        gatekeeper();
        action_gatekeeper();

        $user_id = get_input('guid');
        $user = $user_id ?  get_entity($user_id) : get_loggedin_user();

        if (!$user || !$user->canEdit())
        {
            action_error(elgg_echo('org:cantedit'));
        }
        else
        {
            $name = get_input('name');

            // name
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

            // password
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

            // language
            $language = get_input('language');
            if ($language && $language != $user->language)
            {
                $user->language = $language;
                change_viewer_language($user->language);
                system_message(elgg_echo('user:language:success'));
            }

            // email
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
            forward(get_input('from') ?: $user->getURL());
        }
    }

    function action_delete_entity()
    {
        admin_gatekeeper();
        action_gatekeeper();

        $guid = get_input('guid');

        $entity = get_entity($guid);

        if (($entity) && ($entity->canEdit()))
        {
            if ($entity->delete())
                system_message(sprintf(elgg_echo('entity:delete:success'), $guid));
            else
                register_error(sprintf(elgg_echo('entity:delete:fail'), $guid));
        }
        else
            register_error(sprintf(elgg_echo('entity:delete:fail'), $guid));

        forward('pg/admin/user');
    }
}
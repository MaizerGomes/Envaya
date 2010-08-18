<?php

/**
 * Perform standard authentication with a given username and password.
 * Returns an ElggUser object for use with login.
 *
 * @see login
 * @param string $username The username, optionally (for standard logins)
 * @param string $password The password, optionally (for standard logins)
 * @return ElggUser|false The authenticated user object, or false on failure.
 */
function authenticate($username, $password)
{
    if ($username && $password)
    {
        if ($user = get_user_by_username($username))
        {
            if ($user->isBanned())
            {
                return false;
            }

            if ($user->password == $user->generate_password($password))
            {
                return $user;
            }

            $user->log_login_failure();
            $user->save();
        }
    }

    return false;

}

/**
 * Logs in a specified ElggUser. For standard registration, use in conjunction
 * with authenticate.
 *
 * @see authenticate
 * @param ElggUser $user A valid Elgg user object
 * @param boolean $persistent Should this be a persistent login?
 * @return true|false Whether login was successful
 */
function login(ElggUser $user, $persistent = false)
{
    if ($user->isBanned())
        return false;

    if ($user->check_rate_limit_exceeded())
        return false;

    Session::set('guid', $user->getGUID());

    if ($persistent)
    {
        session_set_cookie_params(60 * 60 * 24 * 60);
    }

    // Users privilege has been elevated, so change the session id (help prevent session hijacking)
    session_regenerate_id(true);

    EventRegister::trigger_event('login','user',$user);
    
    $user->reset_login_failure_count();
    $user->last_login = time();
    $user->last_action = time();
    $user->save();    

    return true;
}


function logout()
{
    $curUser = Session::get_loggedin_user();
    if ($curUser)
    {
        trigger_event('logout','user',$curUser);
    }

    Session::destroy();
    return true;
}

function force_login()
{
    Session::set('last_forward_from', Request::instance()->full_rewritten_url());
    $username = get_input('username');
    if ($username)
    {
        forward("pg/login?username=".urlencode($username));
    }
    else
    {
        forward("pg/login");
    }
}
<?php
	/**
	 * Action for saving a new email address for a user and triggering a confirmation.
	 *
	 * @package Elgg
	 * @subpackage Core
	 * @author Curverider Ltd
	 * @link http://elgg.org/
	 */

	require_once(dirname(dirname(__DIR__)) . "/engine/start.php");
	global $CONFIG;

	gatekeeper();

	$email = get_input('email');
	$user_id = get_input('guid');
	$user = "";

	if (!$user_id)
		$user = get_loggedin_user();
	else
		$user = get_entity($user_id);

	if ($user)
	{
		if (strcmp($email,$user->email)!=0)
		{
			if (!get_user_by_email($email))
			{
				if ($user->email != $email)
                {
					$user->email = $email;
					$user->save();
				}
			}
			else
				register_error(elgg_echo('registration:dupeemail'));
		}
	}
	else
		register_error(elgg_echo('email:save:fail'));

	//forward($_SERVER['HTTP_REFERER']);
	//exit;
?>
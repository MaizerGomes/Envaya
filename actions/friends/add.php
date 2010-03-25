<?php

	/**
	 * Elgg add friend action
	 * 
	 * @package Elgg
	 * @subpackage Core

	 * @author Curverider Ltd

	 * @link http://elgg.org/
	 */

	// Ensure we are logged in
		gatekeeper();
		action_gatekeeper();
		
	// Get the GUID of the user to friend
		$friend_guid = get_input('friend');
		$friend = get_entity($friend_guid);

		$errors = false;
		
	// Get the user
		try {
			if (!get_loggedin_user()->addFriend($friend_guid)) $errors = true;
		} catch (Exception $e) {
			register_error(sprintf(elgg_echo("friends:add:failure"),$friend->name));
			$errors = true;
		}
		if (!$errors){
			system_message(sprintf(elgg_echo("friends:add:successful"),$friend->name));
		}
		
	// Forward to the user friends page
		forward("pg/friends/" . get_loggedin_user()->username . "/");
		
?>
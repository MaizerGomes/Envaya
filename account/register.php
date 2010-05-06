<?php

	/**
	 * Elgg registration page
	 *
	 * @package Elgg
	 * @subpackage Core

	 * @author Curverider Ltd

	 * @link http://elgg.org/
	 */

	/**
	 * Start the Elgg engine
	 */
		require_once(dirname(__DIR__) . "/engine/start.php");

		$friend_guid = (int) get_input('friend_guid',0);
		$invitecode = get_input('invitecode');

	// If we're not logged in, display the registration page
		if (!isloggedin())
        {
            $body = elgg_view_layout('one_column', elgg_view_title(elgg_echo("register")), elgg_view("account/forms/register", array('friend_guid' => $friend_guid, 'invitecode' => $invitecode)));
			page_draw(elgg_echo('register'), $body);
	// Otherwise, forward to the index page
		} else {
			forward();
		}

?>
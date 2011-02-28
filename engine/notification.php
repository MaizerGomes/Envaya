<?php

/*
 * Defines constants for an organization's notification settings 
 * (e.g. which emails they subscribe to)
 */
class Notification
{
	const Batch = 1;    // emails sent by administrators to all users
	const Comments = 2; // emails sent when someone leaves a comment on one of their news updates
	
	static function all()
	{
		return array(static::Batch, static::Comments);
	}
	
	static function get_options()
	{
		return array(
			static::Batch => __('email:subscribe_reminders'),
			static::Comments => __('email:subscribe_comments'),
		);
	}

}
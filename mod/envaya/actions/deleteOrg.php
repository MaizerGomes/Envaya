<?php
	global $CONFIG;
	action_gatekeeper();
	
	$guid = (int)get_input('org_guid');
	$entity = get_entity($guid);
	
	if (($entity) && ($entity instanceof Organization))
	{
		if ($entity->canEdit() && $entity->delete())
			system_message(elgg_echo('org:deleted'));
		else
			register_error(elgg_echo('org:notdeleted'));
	}
	else
		register_error(elgg_echo('org:notdeleted'));
		
	$url_name = $_SESSION['user']->username;
	forward("{$vars['url']}pg/org/member/{$url_name}");
?>
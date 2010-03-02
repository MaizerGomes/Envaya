<?php
	/**
	 * ElggEntity default view.
	 * 
	 * @package Elgg
	 * @subpackage Core
	 * @author Curverider Ltd
	 * @link http://elgg.org/
     */
	    
    if (!$vars['entity']->userCanSee())
    {
        echo " ";
    }
    else if ($vars['full']) 
    {
		echo elgg_view('export/entity', $vars);
	} 
    else 
	{        
		$icon = elgg_view(
				'graphics/icon', array(
				'entity' => $vars['entity'],
				'size' => 'small',
			)
		);
		
		
		$title = $vars['entity']->title;
		if (!$title) $title = $vars['entity']->name;
		if (!$title) $title = get_class($vars['entity']);
			
		$controls = "";
		if ($vars['entity']->canEdit())
		{
            $controls .= "(".elgg_view('output/confirmlink', array(
                'text' => elgg_echo('delete'), 
                'is_action' => true,
                'href' => "{$vars['url']}action/entities/delete?guid={$vars['entity']->guid}"
            )).")";        
		}
		
		$info = "<div><p><b><a href=\"" . $vars['entity']->getUrl() . "\">" . escape($title) . "</a>" . (!$vars['entity']->isApproved() ? (" (" . elgg_echo('org:shortnotapproved') .") ") : "") . "</b> $controls </p></div>";
		
		if (get_input('search_viewtype') == "gallery") {
			
			$icon = "";
			
		} 
        
        $icon = "<a href=\"" . $vars['entity']->getUrl() . "\">$icon</a>";
				
		echo elgg_view_listing($icon, $info);
	}
<?php

    $user_id = get_input('guid');    
    $org = get_entity($user_id);

    if ($org && $org instanceof Organization && $org->canEdit())
    {
        $theme = get_input('theme');

        if ($theme != $org->theme)
        {
            system_message(elgg_echo("theme:changed"));
            $org->theme = $theme;
            $org->save();
            forward($org->getURL());
        }    
    }
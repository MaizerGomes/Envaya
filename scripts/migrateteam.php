<?php
    require_once("scripts/cmdline.php");
    require_once("engine/start.php");

    $tms = get_data("SELECT DISTINCT(container_guid) FROM entities WHERE subtype = ?", array(T_team_member));
    
    foreach ($tms as $tm)
    {
        $org = get_entity($tm->container_guid);

        if ($org)
        {
            $content = '';
            foreach ($org->getTeamMembers() as $teamMember)
            {
                $content .= view_entity($teamMember);
            }

            $teamWidget = $org->getWidgetByName('team');
            $teamWidget->setContent($content, true);
            $teamWidget->save();
        }
    }
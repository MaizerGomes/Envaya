<?php

    $org = $vars['org'];
    $loggedInOrg = Session::get_logged_in_user();

    if ($loggedInOrg instanceof Organization)
    {
        $controls = array();
        if ($org->email)
        {
            $controls[] = "<a href='{$org->get_url()}/send_message'>".__('message:link')."</a>";
        }
        
        
        if (Relationship::query_for_user($loggedInOrg)->where('subject_guid = ?', $org->guid)->is_empty())
        {        
            $networkPage = Widget_Network::get_or_new_for_entity($loggedInOrg);            
            $controls[] = view('widgets/network_add_relationship_link', array(
                'widget' => $networkPage, 
                'org' => $org, 
                'type' => Relationship::Partnership
            ));
        }
            
        if (sizeof($controls))
        {
            echo "<table class='commBox'><tr><td class='commBoxLeft'>&nbsp;</td>";
            
            foreach ($controls as $control)
            {
                echo "<td class='commBoxMain'>$control</td>";
            }
            
            echo "<td class='commBoxRight'>&nbsp;</td></table>";
        }
    }
    
    
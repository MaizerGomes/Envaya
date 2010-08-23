<?php

    echo "<div class='thin_column'><div id='heading'>";

    $org = $vars['org'];
    $subtitle = @$vars['subtitle'];
        
    $icon = $org->get_icon('medium');
    $link = rewrite_to_current_domain($org->get_url());
    
    $escTitle = escape($org->name);
    
    if ($icon)
    {
        $img = "<img src='$icon' alt='$escTitle' />";
        if ($link)
        {
            $img = "<a href='$link'>$img</a>";
        }
        echo $img;
    }

    $hclass = ($icon) ? 'withicon' : 'withouticon';

    $h1 = "<h2 class='$hclass'>$escTitle</h2>";

    if ($link)
    {
        $h1 = "<a href='$link'>$h1</a>";
    }

    echo $h1;      

    if ($subtitle)
    {
        echo "<h3 class='$hclass'>".escape($subtitle)."</h3>";
    }        
    
    echo "</div><div style='clear:both'></div></div>";
?>
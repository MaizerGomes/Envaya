<div id='heading'>
<?php

    $org = $vars['org'];
    $subtitle = @$vars['subtitle'];
            
    $link = rewrite_to_current_domain($org->get_url());
    
    $escTitle = escape($org->name);
   
    $h1 = "<h2>$escTitle</h2>";
    if ($link)
    {
        $h1 = "<a href='$link'>$h1</a>";
    }

    echo $h1;      

    if ($subtitle)
    {
        echo "<h3 class='$hclass'>".escape($subtitle)."</h3>";
    }        
?>
</div>
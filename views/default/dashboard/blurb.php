<?php

    $user = get_loggedin_user();

    if ($user instanceof Organization)
    {
        echo elgg_view("org/dashboard", array('org' => $user));
    }
    else if ($user->admin)
    {
        ?>
        <div class='padded'>
        <ul>
        <li><a href='org/contact'>List of Organizations</a></li>
        <li><a href='pg/admin/statistics'>Statistics</a></li>
        <li><a href='pg/admin/user'>User Administration</a></li>
        <li><a href='pg/logbrowser'>Log Browser</a></li>
        </ul>
        </div>
        <?php
    }
    else
    {
        echo "<div class='padded'>You are not an organization!</div>";
    }


?>
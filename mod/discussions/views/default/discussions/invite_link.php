<?php
    $topic = $vars['topic'];
    $org = $topic->get_container_user();
    
    echo view('js/share');
    echo " <a style='font-weight:bold;white-space:nowrap' href='javascript:emailShare(".json_encode($org->username).");'>" 
        . __('discussions:invite_link') . "</a>";
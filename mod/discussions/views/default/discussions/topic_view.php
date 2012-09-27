<?php
    $topic = $vars['topic'];
    $org = $topic->get_container_entity();
       
    $limit = 20;
    $offset = Input::get_int('offset');
    
    $query  = $topic->query_messages()->show_disabled(true)->limit($limit, $offset);    
    $count = $topic->num_messages;
    $messages = $query->filter();    
    
    echo "<div class='section_content padded'>";
    echo "<h3 style='padding-bottom:8px'>".escape($topic->render_property('subject'))."</h3>";
    
    $items = array();
    
    foreach ($messages as $message)
    {
        $items[] = view('discussions/topic_view_message', array(
            'message' => $message,
            'topic' => $topic,
            'offset' => $offset
        ));    
    }
    
    echo view('paged_list', array(
        'offset' => $offset,
        'limit' => $limit,
        'count' => $count,
        'items' => $items,
        'separator' => "<div style='margin:10px 0px' class='separator'></div>"
    ));
    
    echo "<br />";
        
    $widget = Widget_Discussions::get_for_entity($org);    
    
    if ($widget)
    {
        echo "<div style='float:right'>";    
        echo "<a href='{$widget->get_url()}'>".__('discussions:back_to_topics'). "</a>";
        echo "</div>";
    }
    
    if (!@$vars['show_add_message'])
    {
        echo "<h3><a id='add_message' href='{$topic->get_url()}/add_message?offset={$offset}#add_message'>";
        echo __('discussions:add_message');
        echo "</a></h3>";
        echo view('js/share');
        echo "<h3><a rel='nofollow' href='javascript:emailShare(".json_encode($org->username).")' onclick='ignoreDirty()'>";
        echo __('discussions:invite_link');
        echo "</a></h3>";
    }
    else
    {
        echo "<h3><div id='add_message' style='padding-bottom:8px;'>";
        echo __('discussions:add_message');
        echo " <a href='{$topic->get_url()}?offset={$offset}#add_message'>(".__('hide').")</a>";
        echo "</div></h3>";    
        echo view('discussions/add_message_form', $vars);    
    }        
    echo "</div>";
?>

<?php
    $widget = $vars['widget'];
    $org = $widget->get_container_entity();
    
    $limit = 20;
    $offset = (int)get_input('offset');
    
    $query = DiscussionTopic::query_for_user($org)->limit($limit, $offset);
    
    $topics = $query->filter();
    $count = $query->count();
        
    ob_start();

    if ($count == 0)
    {
        echo "<div class='instructions'>".__('discussions:about')."</div>";
    }

    echo view('paged_list', array(
        'offset' => $offset,
        'limit' => $limit,
        'count' => $count,
        'items' => array_map(function($topic) use ($widget) {
            return view('widgets/discussions_edit_topic_item', array(
                'topic' => $topic,
                'widget' => $widget,
            ));
        }, $topics),
        'separator' => "<div style='padding:5px'></div>"
    ));
    
    echo "<br />";
    echo "<strong><a href='{$org->get_url()}/topic/new'>".__('discussions:add_topic')."</a></strong>";
       
    $content = ob_get_clean();

    echo "<div class='section_content padded'>$content</div>";

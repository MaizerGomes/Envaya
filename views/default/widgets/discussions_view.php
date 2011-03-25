<?php
    $widget = $vars['widget'];
    $org = $widget->get_container_entity();

    $limit = 20;
    $offset = (int)get_input('offset');
    
    $query = $org->query_discussion_topics()->limit($limit, $offset);
    
    $topics = $query->filter();
    $count = $query->count();
        
    ob_start();
        
        
    if ($count > 0)
    {                    
        echo view('paged_list', array(
            'offset' => $offset,
            'limit' => $limit,
            'count' => $count,
            'entities' => $topics,
            'separator' => "<div style='padding:5px'></div>"
        ));
    }        
    else
    {
        echo __('discussions:no_topics');
    }
    
    echo "<br />";
    echo "<strong><a href='{$org->get_url()}/topic/new'>".__('discussions:add_topic')."</a></strong>";
    
    $content = ob_get_clean();
    
    echo "<div class='section_content padded'>$content</div>";
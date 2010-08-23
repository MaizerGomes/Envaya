<?php

    $widget = $vars['widget'];

    echo view_layout('section', __("org:mission"), $widget->render_content());

    $org = $vars['widget']->get_container_entity();

    echo "<div class='section_header'>".__("widget:news:latest")."</div>";

    $items = $org->query_feed_items()->limit(6)->filter();

    echo "<div class='section_content'>";

    echo view('feed/self_list', array('items' => $items));

    echo "</div>";

    $sectors = $org->get_sectors();

    if (!empty($sectors))
    {
        echo view_layout('section', __("org:sectors"),
            view("org/sectors", array('sectors' => $sectors, 'sector_other' => $org->sector_other))
        );
    }

    ob_start();
        $zoom = $widget->zoom ?: 10;

        $lat = $org->get_latitude();
        $long = $org->get_longitude();
        echo view("org/map", array(
            'lat' => $lat,
            'long' => $long,
            'zoom' => $zoom,
            'pin' => true,
            'static' => true
        ));
        echo "<div style='text-align:center'>";
        echo "<em>";
        echo escape($org->get_location_text());
        echo "</em>";
        echo "<br />";
        echo "<a href='org/browse/?lat=$lat&long=$long&zoom=10'>";
        echo __('org:see_nearby');
        echo "</a>";
        echo "</div>";
    $map = ob_get_clean();
    echo view_layout('section', __("org:location"), $map);

?>

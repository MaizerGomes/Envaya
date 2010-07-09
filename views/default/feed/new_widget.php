<?php

    $item = $vars['item'];
    $mode = $vars['mode'];
    $org = $item->getUserEntity();
    $orgUrl = $org->getURL();

    $widget = $item->getSubjectEntity();
    $widgetUrl = $widget->getURL();

    echo "<div style='padding-bottom:5px'>";
    echo sprintf(elgg_echo('feed:new_widget'),
        $mode == 'self' ? escape($org->name) : "<a class='feed_org_name' href='$orgUrl'>".escape($org->name)."</a>",
        "<a href='$widgetUrl'>{$widget->getTitle()}</a>"
    );
    echo "</div>";

    $maxLength = 300;

    $content = translate_field($widget,'content');

    echo "<div>";

    if ($widget->data_types & DataType::HTML)
    {
        echo $content;
    }
    else
    {
        echo elgg_view('output/longtext',
            array('value' => get_snippet($content, $maxLength))
        );
    }

    if (strlen($content) > $maxLength)
    {
        echo " <a class='feed_more' href='$widgetUrl'>".elgg_echo('feed:more')."</a>";
    }
    echo "</div>";

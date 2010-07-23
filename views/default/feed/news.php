<?php

    $item = $vars['item'];
    $mode = $vars['mode'];
    $org = $item->getUserEntity();
    $orgUrl = $org->getURL();

    $update = $item->getSubjectEntity();
    $url = rewrite_to_current_domain($update->getURL());

    if ($update->hasImage())
    {
        echo "<a class='feed_image_link' href='$url'><img src='{$update->thumbnail_url}' /></a>";
    }

    echo "<div style='padding-bottom:5px'>";
    echo sprintf(__('feed:news'),
        $mode == 'self' ? escape($org->name) : "<a class='feed_org_name' href='$orgUrl'>".escape($org->name)."</a>",
        "<a href='$url'>".escape(__('widget:news:item'))."</a>"
    );
    echo "</div>";

    $maxLength = 350;

    $content = $update->renderContent();

    echo "<div class='feed_snippet'>";
    echo get_snippet($content, $maxLength);

    if (strlen($content) > $maxLength)
    {
        echo " <a class='feed_more' href='$url'>".__('feed:more')."</a>";
    }
    echo "</div>";
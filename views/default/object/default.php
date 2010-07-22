<?php
    /**
     * ElggEntity default view.
     *
     * @package Elgg
     * @subpackage Core
     * @author Curverider Ltd
     * @link http://elgg.org/
     */

    $icon = elgg_view(
            'graphics/icon', array(
            'entity' => $vars['entity'],
            'size' => 'small',
        )
    );


    $title = $vars['entity']->title;
    if (!$title) $title = $vars['entity']->name;
    if (!$title) $title = get_class($vars['entity']);

    $controls = "";
    if ($vars['entity']->canEdit())
    {
        $controls .= " (<a href=\"admin/delete_entity?guid={$vars['entity']->guid}\">" . __('delete') . "</a>)";
    }

    $info = "<div><p><b><a href=\"" . $vars['entity']->getUrl() . "\">" . escape($title) . "</a></b> $controls </p></div>";

    if (get_input('search_viewtype') == "gallery") {

        $icon = "";

    }

    $owner = $vars['entity']->getOwnerEntity();
    $ownertxt = __('unknown');

    $info = "<span>$info</span>";
    $icon = "<span>$icon</span>";

    echo elgg_view_listing($icon, $info);

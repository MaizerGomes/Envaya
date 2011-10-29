<?php
    $user = $vars['user'];
?>
<table style='width:100%'>
<tr>
<td>
    <a class='icon_link icon_home' href='<?php echo $user->get_url() ?>'><?php echo __('dashboard:view_home') ?></a>
    <div class='icon_separator'></div>
    <a class='icon_link icon_design' href='<?php echo $user->get_url() . "/design" ?>?from=/pg/dashboard'><?php echo __('design:edit') ?></a>
    <div class='icon_separator'></div>
    <a class='icon_link icon_settings' href='<?php echo $user->username ?>/settings'><?php echo __('dashboard:settings') ?></a>
    <div class='icon_separator'></div>
    <a class='icon_link icon_photos' href='<?php echo $user->get_url() . "/addphotos" ?>?from=/pg/dashboard&t=<?php echo timestamp(); ?>'><?php echo __('upload:photos:title') ?></a>
    <div class='icon_separator'></div>
    <a class='icon_link icon_help' href='/envaya/page/help'><?php echo __('help') ?></a>
</td>
<td>
    <a class='icon_link icon_explore' href='/pg/browse'><?php echo __("browse:title") ?></a>
    <div class='icon_separator'></div>
    <a class='icon_link icon_search' href='/pg/search'><?php echo __("search:title") ?></a>
    <div class='icon_separator'></div>
    <a class='icon_link icon_feed' href='/pg/feed'><?php echo __("feed:title") ?></a>
    <div class='icon_separator'></div>
    <a class='icon_link icon_logout' href='/pg/logout'><?php echo __('logout') ?></a>
</td>
</tr>
</table>

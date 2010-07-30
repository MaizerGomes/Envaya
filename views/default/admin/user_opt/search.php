<?php
    /**
     * Elgg user search box.
     *
     * @package Elgg
     * @subpackage Core
     * @author Curverider Ltd
     * @link http://elgg.org/
     */
?>
<div id="search-box">
    <form action="admin/search" method="get">
    <b><?php echo __('admin:user:label:search'); ?></b>
    <?php

        echo elgg_view('input/text',array('internalname' => 'tag'));

    ?>
    <input type="hidden" name="object" value="user" />
    <input type="submit" name="search" value="<?php echo __('search:submit'); ?>" />
    </form>
</div>

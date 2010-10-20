<?php
    $feedItem = $vars['item'];
    $org = $feedItem->get_user_entity();
    $subject = $feedItem->get_subject_entity();
    $mode = $vars['mode'];
        
    if ($org && $subject)
    {
        $orgIcon = $org->get_icon('small');
        $orgUrl = $org->get_url();
?>

    <div class='blog_post_wrapper padded'>
    <div class="feed_post">
        <?php if ($mode != 'self') { 
            echo view('feed/icon', array('org' => $org));            
        } ?>
        <div class='feed_content'>
        <?php
            echo $feedItem->render_thumbnail($mode);
            echo "<div style='padding-bottom:5px'>";
            echo $feedItem->render_heading($mode);
            echo "</div>";
            echo $feedItem->render_content($mode);
        ?>
        <div class='blog_date'><?php echo $feedItem->get_date_text() ?></div>
        <?php
        if (Session::isadminloggedin()) {
            echo "<span class='admin_links'>";
            echo view('output/confirmlink', array(
                'is_action' => true,
                'href' => "/admin/delete_feed_item?item={$feedItem->id}",
                'text' => __('delete')
            ));
            echo "</span>";
        }
        ?>
        </div>
        <div style='clear:both'></div>
    </div>
    </div>
<?php
    }

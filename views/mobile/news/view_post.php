<div class='section_content'>
<?php

    $post = $vars['post'];              
    $url = rewrite_to_current_domain($post->get_url());
    $org = $post->get_root_container_entity();
        
    echo view_entity($post, array('single_post' => true));
    
    if ($org->query_widgets()->count() > 1)
{

?>
<div style='text-align:center'>
<a href='<?php echo "$url/prev"; ?>'>&lt; <?php echo __('previous'); ?></a>
<a href='<?php echo "$url/next"; ?>'><?php echo __('next'); ?> &gt;</a>
</div>
<?php
}
?>


</div>
</div>
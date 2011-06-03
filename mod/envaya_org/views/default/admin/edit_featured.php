<div class='padded'>
<?php
    $entity = $vars['entity'];
    $org = $entity->get_container_entity();
?>
<form method='POST' action='/admin/envaya/edit_featured'>
<?php echo view('input/securitytoken') ?>

<strong><a href='<?php echo $org->get_url() ?>'><?php echo escape($org->name) ?></a></strong>

<br /><br />

<div class='input'>
<label><?php echo __('featured:image'); ?></label>
<?php echo view('admin/featured_image', array(
    'name' => 'image_url',
    'org' => $org, 
    'value' => $entity->image_url
)); 
?>
</div>
<div class='input'>
<label><?php echo __('featured:text'); ?></label>
<?php

    echo view('input/tinymce',
        array(
            'name' => 'content',
            'value' => $entity->content,
            'track_dirty' => true
        )
    );
    
?>
</div>
<?php

    echo view('input/submit', array('value' => __('savechanges')));

    echo view('input/hidden', array(
        'name' => 'guid',
        'value' => $entity->guid
    ));
    
    
?>
</form>
</div>
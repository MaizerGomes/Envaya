<div class='section_content padded'>
<?php

$widget = $vars['widget'];
$org = $widget->get_root_container_entity();

ob_start();
?>
<label><?php echo __('register:location') ?></label>
<div>
<?php echo __('register:city') ?> <?php echo view('input/text', array(
    'name' => 'city',
    'style' => 'width:200px',
    'track_dirty' => true,
    'value' => $org->city
)) ?>, <?php echo escape($org->get_country_text()); ?>
</div>
<div>
<?php echo __('register:region') ?> <?php echo view('input/pulldown', array(
    'name' => 'region',
    'options' => Geography::get_region_options($org->country),
    'empty_option' => __('register:region:blank'),
    'value' => $org->region
)) ?>
<br />
<br />
<?php
    $lat = $org->get_latitude() ?: 0.0;
    $long = $org->get_longitude() ?: 0.0;

    $zoom = $widget->get_metadata('zoom') ?: (($lat || $long) ? 11 : 1);

    echo view("output/map", array(
        'lat' => $lat,
        'long' => $long,
        'zoom' => $zoom,
        'pin' => true,
        'edit' => true
    ));
    
    $content = ob_get_clean();
    
    echo view("widgets/edit_form", array(
        'widget' => $widget,
        'body' => $content
    )); 
?>

</div>
</div>

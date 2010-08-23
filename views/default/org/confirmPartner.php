<?php
    $org = $vars['entity'];
    $partner = $vars['partner'];

?>
<div class='section_content padded'>
<form action='<?php echo $partner->get_url() ?>/create_partner' method='POST'>
<?php echo view('input/securitytoken') ?>
<div class="partnership_view">
    <a class='feed_org_icon' href='<?php echo $partner->get_url() ?>'><img src='<?php echo $partner->get_icon('small') ?>' /></a>

    <div class='feed_content'>
        <a class='feed_org_name' href='<?php echo $partner->get_url() ?>'><?php echo escape($partner->name); ?></a><br />
    </div>
    <div style='clear:both;'></div>

</div>
<label><?php echo __('partner:confirm:instructions') ?></label>
<div>
<?php

echo view('input/submit',array(
    'value' => __('partner:confirm:button')
));

?>
</div>


</form>
</div>

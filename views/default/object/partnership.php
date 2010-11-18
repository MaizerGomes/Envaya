<?php
  
    $entity = $vars['entity'];
    $url = $entity->get_url();    
    $partner = $entity->get_partner();

?>
   
<div class="partnership_view">    
    <a class='feed_org_icon' href='<?php echo $partner->get_url() ?>'><img src='<?php echo $partner->get_icon('small') ?>' /></a>
    <div class='feed_content'>
    <a class='feed_org_name' href='<?php echo $partner->get_url() ?>'><?php echo escape($partner->name); ?></a><?php echo (($entity->description) ? ":" : ""); ?>
    <span><?php    
        echo view('output/longtext', array('value' => $entity->translate_field('description'))); 
    ?></span>
    </div>
<div style='clear:both;'></div>        
</div>

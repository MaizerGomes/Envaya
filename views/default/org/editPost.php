<?php
    $blog = $vars['entity'];    

    $body = $vars['entity']->content;

    ob_start();                
?>
<div class='input'>
    <label><?php echo elgg_echo('blog:content:label') ?></label>
    <?php echo elgg_view('input/longtext', array('internalname' => 'blogbody', 'trackDirty' => true, 'value' => $body)) ?>
</div>

<div class='input'>
<label><img src='_graphics/attach_image.gif?v2' style='vertical-align:middle' /> <?php echo elgg_echo('blog:image:label') ?></label><br />
<?php echo elgg_view('input/image', array(
        'current' => ($blog && $blog->hasImage() ? $blog->getImageUrl('small') : null),
        'internalname' => 'image',
        'trackDirty' => true,
        'sizes' => NewsUpdate::getImageSizes(),
        'deletename' => 'deleteimage',
    )) ?>    
</div>

<?php
    echo elgg_view('input/hidden', array('internalname' => 'blogpost', 'value' => $vars['entity']->getGUID()));
    echo elgg_view('input/alt_submit', array(
            'internalname' => "delete", 
            'internalid' => 'widget_delete', 
            'trackDirty' => true,
            'confirmMessage' => elgg_echo('blog:delete:confirm'),            
            'value' => elgg_echo('blog:delete')
        )); 

    echo elgg_view('input/submit', array('internalname' => 'submit', 'trackDirty' => true, 'value' => elgg_echo('savechanges'))); 
?>

<?php
    $form_body = ob_get_clean();
    echo elgg_view('input/form', array('action' => "action/org/editPost", 'enctype' => "multipart/form-data", 'body' => $form_body));
?>
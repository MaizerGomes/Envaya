<?php
    $blog = $vars['entity'];

    $body = $vars['entity']->content;

    ob_start();
?>
<div class='input'>
    <?php echo elgg_view('input/tinymce', array(
        'internalname' => 'blogbody',
        'trackDirty' => true,
        'valueIsHTML' => $blog->hasDataType(DataType::HTML),
        'value' => $body)) ?>
</div>


<?php
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
    echo elgg_view('input/form', array('action' => "{$vars['entity']->getURL()}/save", 'enctype' => "multipart/form-data", 'body' => $form_body));
?>
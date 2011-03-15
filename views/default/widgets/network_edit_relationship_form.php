<?php

    $relationship = $vars['relationship'];
    $org = $relationship->get_subject_organization();

echo view('input/securitytoken');
echo view('input/hidden', array('name' => 'guid', 'value' => $relationship->guid));

if (!$org)
{
?>

<table class='inputTable' style='margin:0 auto'>
<tr><th><?php echo __('network:search_name'); ?></th>
<td><?php echo view('input/text', array('name' => 'name', 'value' => $relationship->subject_name, 'trackDirty' => true, 'id' => 'name')); ?></td></tr>
<tr><th><?php echo __('network:search_email'); ?></th>
<td><?php echo view('input/text', array('name' => 'email', 'value' => $relationship->subject_email, 'trackDirty' => true,  'id' => 'email')); ?></td></tr>
<tr><th><?php echo __('network:search_website'); ?></th>
<td><?php echo view('input/text', array('name' => 'website', 'value' => $relationship->subject_website,  'trackDirty' => true, 'id' => 'website')); ?></td></tr>
</table>    
<?php
}
else
{
    echo view_entity($org);
}

echo "<div style='padding-top:5px'><em>".sprintf(__('network:describe_relationship'), escape($relationship->get_subject_name()))."</em></div>";
echo view('input/tinymce', array('name' => 'content', 'trackDirty' => true, 'value' => $relationship->content));
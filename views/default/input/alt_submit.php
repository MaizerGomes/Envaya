<script type='text/javascript'>
function setHiddenSubmit($id)
{
    document.getElementById($id).value = "1";
    return true;
}
</script>
<?php

     /*
        Allows you to determine which submit button was clicked in IE6
     */

    $hidden_id = "_alt_submit".mt_rand();

    echo view('input/hidden', array(
        'name' => $vars['name'],
        'id' => $hidden_id,
        'value' => '',
    ));

    $js = "onclick='return true ";

    if (@$vars['confirmMessage'])
    {
        $js .= "&& confirm(".json_encode(@$vars['confirmMessage']).")";
    }
    if (@$vars['trackDirty'])
    {
        $js .= "&& setSubmitted() ";
    }
    $js .= "&& setHiddenSubmit(".json_encode($hidden_id).");'";

    echo view('input/submit', array(
        'name' => "_alt_submit",
        'id' => @$vars['id'],
        'js' => $js,
        'trackDirty' => @$vars['trackDirty'],
        'class' => @$vars['class'],
        'value' => @$vars['value']
    ));

?>
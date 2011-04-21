<div class='section_content padded'>
<?php
    $org = $vars['org'];

?>
<form action='<?php echo $org->get_url() ?>/add_page' method='POST'>
<?php
    echo view('input/securitytoken');     
    echo view('widgets/edit_title');
?>

<div class='input'>
<label><?php echo __('widget:address'); ?></label>
<div class='websiteUrl'>
<?php echo $org->get_url() . "/page/" . view('input/text', array('name' => 'widget_name', 'id' => 'widget_name', 'js' => "style='width:200px'")); 
?>
</div>
</div>

<?php echo view('focus', array('id' => 'title')); ?>
<script type='text/javascript'>
(function() {

var widgetName = document.getElementById('widget_name');
var title = document.getElementById('title');
var autoFill = true;

function makeWidgetName(value)
{
    return value.toLowerCase().replace(/\s+/g, '-').replace(/[^\w\-]/g, '');
}

addEvent(title, 'keypress', function() {
    setTimeout(function() {
        if (autoFill)
        {
            widgetName.value = makeWidgetName(title.value);
        }
    }, 1);
});
addEvent(widgetName, 'keypress', function() {
    autoFill = false;
});

})();
</script>

<?php 
    echo view('widgets/edit_initial_content');
    echo view('input/submit', array('trackDirty' => true, 'value' => __('widget:create'))); ?>

</form>
</div>
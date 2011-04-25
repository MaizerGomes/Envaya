<?php

$entity = $vars['entity'];
$property = $vars['property'];
$isHTML = $vars['isHTML'];

$org = $entity->get_root_container_entity();

$text = $entity->$property;

$height = 300;

ob_start();

echo "<table class='gridView' style='width:1100px;margin:0 auto'><tr><td  style='width:50%;padding-right:10px'>";
echo "<h3>".sprintf(__("trans:original_in"),
     view('input/language', array(
        'name' => 'language',
        'value' => $entity->get_language()
    ))
).": </h3>";

if ($isHTML)
{
    $leftHeight = $height - 30;
    echo "<div style='height:{$leftHeight}px;border:1px solid black;padding:5px;margin-top:25px;overflow:auto'>";
    echo Markup::sanitize_html($text);
    echo "</div>";
}
else
{
    echo view("output/longtext", array('value' => $text));
}
echo "</td><td style='width:50%'>";

$lang = $vars['targetLang'];
$langStr = __($lang, $lang);

$curTranslation = $entity->lookup_translation($property, $entity->get_language(), $lang, TranslateMode::ManualOnly, $isHTML);
if (!$curTranslation->value)
{
    $curTranslation = $entity->lookup_translation($property, $entity->get_language(), $lang, TranslateMode::All, $isHTML);
}

$curText = $curTranslation->value ?: $text;

$transIn = sprintf(__("trans:inlang"), view('input/language', array(
        'name' => 'newLang',
        'value' => $lang
    ))
);

echo "<h3>$transIn: </h3>";

if ($isHTML)
{
    echo view("input/tinymce", array(
        'name' => 'translation',
        'height' => $height,
        'value' => $curText));
}
else
{    
    if (strlen($enText) > 50 || strpos($enText, "\n") !== FALSE)
    {
       $input = "input/longtext";
       $js = "style='height:".(30+floor(strlen($enText)/50)*25)."px'";
    }
    else
    {
        $input = "input/text";
        $js = '';
    }

    echo view($input, array('name' => 'translation', 'value' => $curText, 'js' => $js));
}

echo "<br>".
    view("input/hidden", array('name' => 'entity_guid', 'value' => $entity->guid)).
    view("input/hidden", array('name' => 'property', 'value' => $property)).
    view("input/hidden", array('name' => 'html', 'value' => $isHTML)).
    view("input/hidden", array('name' => 'from', 'value' => $vars['from'])).
    view('input/alt_submit', array(
        'name' => "delete",
        'id' => 'widget_delete',
        'trackDirty' => true,
        'confirmMessage' => __('areyousure'),
        'value' => __('delete')
    )).
    view('input/submit', array('value' => __('trans:submit')));

echo "</td></tr></table>";

$formBody = ob_get_clean();

echo view('input/form', array('action' => "/tr/save_translation", 'body' => $formBody));
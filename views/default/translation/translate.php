<?php

$entity = $vars['entity'];
$property = $vars['property'];

$org = $entity->getRootContainerEntity();

$text = $entity->get($property);

ob_start();

echo "<table class='gridView' style='width:900px;margin:0 auto'><tr><td  style='width:50%'>";
echo "<div class='input'>";
echo "<h3>".sprintf(elgg_echo("trans:original_in"),
     elgg_view('input/language', array(
        'internalname' => 'language',
        'value' => $entity->getLanguage()
    ))
).": </h3>";
echo elgg_view("output/longtext", array('value' => $text));
echo "</div>";
echo "</td><td style='width:50%'>";

$lang = get_language();
$langStr = elgg_echo($lang, $lang);

$curTranslation = lookup_translation($entity, $property, $entity->getLanguage(), $lang);

$curText = ($curTranslation) ? $curTranslation->value : $text;

$transIn = sprintf(elgg_echo("trans:inlang"), elgg_view('input/language', array(
        'internalname' => 'newLang',
        'value' => $lang
    ))
);

echo "<h3>$transIn: </h3>";
echo elgg_view("input/longtext", array('internalname' => 'translation', 'value' => $curText))."<br>".
    elgg_view("input/hidden", array('internalname' => 'entity_guid', 'value' => $entity->guid)).
    elgg_view("input/hidden", array('internalname' => 'property', 'value' => $property)).
    elgg_view("input/hidden", array('internalname' => 'from', 'value' => $vars['from'])).
    elgg_view('input/submit', array('internalname' => 'submit', 'value' => elgg_echo('trans:submit')));

echo "</td></tr></table>";

$formBody = ob_get_clean();

echo elgg_view('input/form', array('action' => "{$vars['url']}action/translation/translate", 'body' => $formBody));
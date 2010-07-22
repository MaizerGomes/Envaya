<?php

$lang = $vars['lang'];

$query = get_input('q');

$keys = get_translatable_language_keys();

if ($query)
{
    $lq = strtolower($query);

    $filteredKeys = array();
    foreach ($keys as $key)
    {
        if (strpos($key, $lq) !== false
            || strpos(strtolower(__($key, 'en')), $lq) !== false
            || strpos(strtolower(__($key, $lang)), $lq) !== false)
        {
            $filteredKeys[] = $key;
        }
    }
    $keys = $filteredKeys;
}

$edited = get_input('edited');

if ($edited)
{
    $filteredKeys = array();

    $editedKeys = array();
    foreach (InterfaceTranslation::filterByLang($lang) as $itrans)
    {

        if ($itrans->value != @$CONFIG->translations[$lang][$itrans->key])
        {
            $editedKeys[$itrans->key] = true;
        }
    }

    foreach ($keys as $key)
    {
        if (@$editedKeys[$key])
        {
            $filteredKeys[] = $key;
        }
    }

    $keys = $filteredKeys;
}

$limit = 10;
$baseurl = "org/translate_interface?q=".urlencode($query)."&edited=".($edited ? 1 : 0);
$offset = (int)get_input('offset');
$count = sizeof($keys);

$from = urlencode("$baseurl&offset=$offset");

echo "<form method='GET' action='org/translate_interface'>";

echo "<label>".__("trans:filter")."</label><br />";

echo elgg_view('input/text', array('internalname' => 'q', 'value' => $query));
echo elgg_view('input/submit', array('value' => __("search")));
echo "<div class='edited'>";
echo elgg_view('input/checkboxes', array(
    'internalname' => 'edited',
    'options' => array('1' => __('trans:edited_only')),
    'value' => $edited ? '1' : null
));
echo "</div>";

echo "</form>";

echo "<br />";
echo "<h3>";
if ($query)
{
    echo sprintf(__("trans:search"), escape($query));
}
echo "</h3>";

if (empty($keys))
{
    echo __("search:noresults");
}
else
{
    echo elgg_view('navigation/pagination',array(
        'baseurl' => $baseurl,
        'offset' => $offset,
        'count' => $count,
        'limit' => $limit
    ));

    echo "<table class='gridTable'>";

    echo "<tr>";
    echo "<th>".__('trans:key')."</th>";
    echo "<th>".escape(__('en'))."</th>";
    echo "<th>".escape(__($lang))."</th>";

    for ($i = $offset; $i < $offset + $limit && $i >= 0 && $i < $count; $i++)
    {
        $key = $keys[$i];

        $enText = __($key, 'en');

        echo "<tr>";
        echo "<td>".escape($key)."</td>";
        echo "<td>".elgg_view('output/longtext', array('value' => $enText))."</td>";

        $trans = @$CONFIG->translations[$lang][$key];

        $it = InterfaceTranslation::getByKeyAndLang($key, $lang);

        if ($it)
        {
            $val = elgg_view('output/longtext', array('value' => $it->value));
            if ($trans != $it->value)
            {
                $res = "<div class='edited'>$val</div>";
            }
            else
            {
                $res = "<div class='reviewed'>$val</div>";
            }
        }
        else if ($trans)
        {
            $res = elgg_view('output/longtext', array('value' => $trans));
        }
        else
        {
            $res = "<em>".__("trans:none")."</em>";
        }

        echo "<td>$res</td>";
        echo "<td><a href='org/translate_interface?key=$key&from=$from'>".__('edit')."</a></td>";

        echo "</tr>";
    }

    echo "</table>";

    echo elgg_view('navigation/pagination',array(
        'baseurl' => $baseurl,
        'offset' => $offset,
        'count' => $count,
        'limit' => $limit
    ));

    if (isadminloggedin())
    {
        echo "<br /><br /><a href='org/translate_interface?export=1'>".__('trans:export')."</a>";
    }
}

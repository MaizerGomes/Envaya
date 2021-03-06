<?php

$links = array();
$js = view('js/language');
if ($js)
{
    echo "<script type='text/javascript'>$js</script>";
}

foreach (Language::$languages as $lang => $v)
{    
    $name = escape(__("lang:$lang", $lang));

    if (Language::get_current_code() == $lang)
    {
        $links[] = "<strong>$name</strong>";
    }
    else
    {
        $url = url_with_param($vars['original_url'], 'lang', $lang);
        $links[] = "<a href='".escape($url)."' onclick='return setLang(\"$lang\");'>$name</a>";
    }    
}
echo implode(' &middot; ', $links);

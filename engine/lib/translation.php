<?php

class TranslateMode
{
    const None = 1;
    const ManualOnly = 2;
    const All = 3;
}

function get_auto_translation($text, $origLang, $viewLang)
{
    if ($origLang == $viewLang)
    {
        return null;
    }

    $text = trim($text);
    if (!$text)
    {
        return null;
    }

    $ch = curl_init();

    $text = str_replace("\r","", $text);
    $text = str_replace("\n", ",;", $text);

    $url = "ajax.googleapis.com/ajax/services/language/translate?v=1.0&langpair=$origLang%7C$viewLang";

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, "www.envaya.org");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('q' => $text));

    $json = curl_exec($ch);

    curl_close($ch);

    $res = json_decode($json);

    $translated = $res->responseData->translatedText;
    if (!$translated)
    {
        return null;
    }

    $text = html_entity_decode($translated, ENT_QUOTES);

    return str_replace(",;", "\n", $text);
}

function guess_language($text)
{
    if (!$text)
    {
        return null;
    }

    $ch = curl_init();

    $url = "ajax.googleapis.com/ajax/services/language/detect?v=1.0&q=".urlencode(get_snippet($text, 500));

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, "www.envaya.org");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $json = curl_exec($ch);

    curl_close($ch);

    $res = json_decode($json);

    $lang = $res->responseData->language;

    global $CONFIG;

    if (!$lang || !isset($CONFIG->translations[$lang]))
    {
        return null;
    }

    return $lang;
}

function get_translate_mode()
{
    return ((int)get_input("trans")) ?: TranslateMode::ManualOnly;
}

function translate_listener($event, $object_type, $translation)
{
    PageContext::add_available_translation($translation);
}

register_event_handler('translate','all','translate_listener');
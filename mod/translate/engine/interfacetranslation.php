<?php

/*
 * Represents a user-contributed translation (or correction to existing translation)
 * of a piece of text from Envaya's user interface into a particular language.
 *
 * InterfaceTranslations are not directly used for the user interface translations; 
 * first they must be exported and saved as PHP files in languages/
 * 
 * (Contrast with Translation class, which represents a translation of user-generated
 * content.)
 */
class InterfaceTranslation extends Entity
{
    static $table_name = 'interface_translations';
    static $table_attributes = array(
        'language_guid' => 0,
        'value' => '',
        'default_value' => '', // the key's default value when this translation was created; allows detecting stale translations
        'score' => 0,
    );   
    
    function is_stale()
    {
        $key = $this->get_container_entity();
        return $this->default_value != $key->get_default_value();
    }
    
    function save()
    {
        $key = $this->get_container_entity();
        if (!$this->default_value)
        {
            $this->default_value = $key->get_default_value();
        }
    
        if (!$this->language_guid)
        {
            $this->language_guid = $key->language_guid;
        }
        parent::save();
    }
    
    function get_language()
    {
        return InterfaceLanguage::get_by_guid($this->language_guid);
    }
    
    function get_url()
    {
        return $this->get_container_entity()->get_url() . "/{$this->guid}";
    }
    
    function query_votes()
    {
        return TranslationVote::query()->where('container_guid = ?', $this->guid);
    }
}

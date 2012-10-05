<?php

class TranslationKey extends Entity
{
    static $table_base_class = 'TranslationKey';
    static $table_name = 'translation_keys';
    static $table_attributes = array(
        'subtype_id' => '',
        'name' => '',
        'language_guid' => null,
        'num_translations' => 0,
        'best_translation' => '',
        'best_translation_guid' => null,
        'best_translation_hash' => '',
        'best_translation_source' => 0,
        'best_translation_approval' => 0,
    );    
    
    function update($recursive = false)
    {
        $this->num_translations = $this->query_translations()->where('status = ?', Translation::Published)->count();

        $best = $this->query_translations()
            ->where('score >= 0')
            ->where('status = ?', Translation::Published)
            ->order_by('time_created desc, tid desc')
            ->get();
            
        if ($best)
        {
            $this->best_translation = $best->value;
            $this->best_translation_guid = $best->guid;
            $this->best_translation_hash = $best->default_value_hash;
            $this->best_translation_approval = $best->approval;
            $this->best_translation_source = $best->source;
        }
        else
        {
            $this->best_translation = '';
            $this->best_translation_hash = '';
            $this->best_translation_guid = null;
            $this->best_translation_source = 0;
        }
        $this->save();
    }
    
    function get_language()
    {
        return TranslationLanguage::get_by_guid($this->language_guid);
    }
    
    public function new_translation()
    {
        $translation = new Translation();   
        $translation->container_guid = $this->guid;
        return $translation;
    }
    
    function get_title()
    {
        return $this->name;
    }
        
    function get_best_translation()
    {
        return Translation::get_by_guid($this->best_translation_guid);
    }
        
    function query_translations()
    {
        return Translation::query()->where('container_guid = ?', $this->guid);
    }

    function query_comments()
    {
        return TranslationKeyComment::query()
            ->where('container_guid = ? OR (key_name = ? AND language_guid is null)', $this->guid, $this->name);
    }    
    
    function get_default_value()
    {
        throw new NotImplementedException(); 
    }
    
    function get_default_value_lang()
    {
        throw new NotImplementedException(); 
    }
    
    function get_behavior()
    {
        throw new NotImplementedException(); 
    }
    
    function get_instance_mixin_classes()
    {
        $mixin_classes = parent::get_instance_mixin_classes();
        $mixin_classes[] = $this->get_behavior();        
        return $mixin_classes;
    }
    
    function get_placeholders()
    {
        return array();
    }
       
    function get_current_base_value()
    {
        return $this->get_default_value();
    }        
    
    function get_current_base_lang()
    {
        return $this->get_language()->get_current_base_code();
    }
    
    function queue_auto_translation()
    {
        TaskQueue::queue_task(array('TranslationKey','fetch_auto_translation_by_guid'), array($this->guid));
    }
    
    static function fetch_auto_translation_by_guid($guid)
    {
        $key = TranslationKey::get_by_guid($guid);
        if ($key)
        {
            $key->fetch_auto_translation();
        }
    }
    
    function fetch_auto_translation()
    {        
        // avoid fetching duplicate translations
        if ($this->query_translations()
            ->where('source = ?', Translation::GoogleTranslate)
            ->where('default_value_hash = ?', $this->get_default_value_hash())
            ->exists())
        {
            return;
        }
        
        $value = $this->get_default_value();
        $base_lang = $this->get_default_value_lang();
        $lang = $this->get_language()->code;
    
        $trans_value = GoogleTranslate::get_auto_translation($value, $base_lang, $lang);
        if ($trans_value != null)
        {
            $auto_trans = $this->new_translation();
            $auto_trans->source = Translation::GoogleTranslate;
            $auto_trans->value = $trans_value;
            $auto_trans->save();
            $this->update();
        }
    }
    
    function get_default_value_hash()
    {
        return sha1($this->get_default_value());
    }
    
    function get_draft_translation_for_user($user)
    {
        if (!$user)
        {
            return null;
        }
        
        return $this->query_translations()
            ->where('status = ?', Translation::Draft)
            ->where('owner_guid = ?', $user->guid)
            ->order_by('tid desc')
            ->get();
    }    
}
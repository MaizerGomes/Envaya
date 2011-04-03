<?php

/*
 * Base class for many types of models. 
 *
 * Each Entity has a guid which is unique even among different entity subclasses.
 * This allows you to specify any subclass instance by guid, without needing to record the subclass separately.
 * This is kind of useful for things like feed items and translations, 
 * which may refer to many different types of entities.
 * 
 * Entities also have an 'status' field which allows effectively deleting rows
 * while leaving them in the database to allow them to be undeleted.
 *
 * Entities can also have metadata, which allows storing/retreiving arbitrary properties (e.g. $entity->foo)
 * without needing to define them in the database schema. Metadata is only fetched when requested.
 * Warning: if you forget to define an attribute, or make a typo, a property might be saved
 * as metadata accidentally.
 *
 * However there are also significant drawbacks to the current implementation,
 * such as that data is split across multiple tables ('entities' and the subclass's $table_name)
 * and a join is required in order to get all the data.
 * 
 */

abstract class Entity extends Model
    implements Loggable, Serializable
{
    protected $metadata_cache = array();        

    static $primary_key = 'guid';    
    static $current_request_entities = array();
    
    function __construct($row = null)
    {
        parent::__construct($row);

        if ($row)
        {
            $this->cache_for_current_request();
        }
    }
    
    static function get_subtype_id()
    {
        return EntityRegistry::get_subtype_id(get_called_class());
    }

    public function get_date_text()
    {
        return friendly_time($this->time_created);
    }    
    
    function cache_for_current_request()
    {
        static::$current_request_entities[$this->guid] = $this;
    }
    
    function clear_from_cache()
    {        
        unset(static::$current_request_entities[$this->guid]);
        get_cache()->delete(static::entity_cache_key($this->guid));
    }        
    
    function save_to_cache()
    {        
        $this->cache_for_current_request();
        get_cache()->set(static::entity_cache_key($this->guid), $this);
    }
    
    static function get_from_cache($guid)
    {
        if (isset(static::$current_request_entities[$guid]))
        {
            return static::$current_request_entities[$guid];
        }
        else
        {
            $entity = get_cache()->get(static::entity_cache_key($guid));
            if ($entity)
            {
                static::$current_request_entities[$guid] = $entity;
                return $entity;
            }
        }
        return null;
    }    
    
    static function entity_cache_key($guid)
    {
        return make_cache_key("entity", $guid);
    }  
    
    protected function init_from_row($row)
    {
        $entityRow = (property_exists($row, 'subtype')) ? $row : get_entity_as_row($row->guid);
        parent::init_from_row($entityRow);
            
        if (!property_exists($row, get_first_key(static::$table_attributes)))
        {
            $objectEntityRow = $this->select_table_attributes($row->guid);
            parent::init_from_row($objectEntityRow);
        }
    }

    protected function initialize_attributes()
    {        
        $this->attributes['subtype'] = static::get_subtype_id();
        $this->attributes['owner_guid'] = 0;
        $this->attributes['container_guid'] = 0;
        $this->attributes['time_created'] = 0;
        $this->attributes['time_updated'] = 0;
        $this->attributes['status'] = EntityStatus::Enabled;
        
        parent::initialize_attributes();
    }

    public function save_table_attributes()
    {
        $tableName = static::$table_name;
    
        $guid = $this->guid;
        if (Database::get_row("SELECT guid from $tableName where guid = ?", array($guid)))
        {
            Database::update_row($tableName, 'guid', $guid, $this->get_table_attributes());
        }
        else
        {
            $values = $this->get_table_attributes();
            $values['guid'] = $guid;
                        
            Database::insert_row($tableName, $values);        
        }
    }

    public function select_table_attributes($guid)
    {
        $tableName = static::$table_name;
        return Database::get_row("SELECT * from $tableName where guid=?", array($guid));
    }

    /**
     * Return the value of a given key.
     * If $name is a key field (as defined in $this->attributes) that value is returned, otherwise it will
     * then look to see if the value is in this object's metadata.
     *
     * Q: Why are we not using __get overload here?
     * A: Because overload operators cause problems during subclassing, so we put the code here and
     * create overloads in subclasses.
     *
     * @param string $name
     * @return mixed Returns the value of a given value, or null.
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->attributes))
        {
            return $this->attributes[$name];
        }
        
        return $this->get_metadata($name);
    }

    /**
     * Set the value of a given key, replacing it if necessary.
     * If $name is a base attribute (as defined in $this->attributes) that value is set, otherwise it will
     * set the appropriate item of metadata.
     *
     * Note: It is important that your class populates $this->attributes with keys for all base attributes, anything
     * not in there gets set as METADATA.
     *
     * Q: Why are we not using __set overload here?
     * A: Because overload operators cause problems during subclassing, so we put the code here and
     * create overloads in subclasses.
     *
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        if (array_key_exists($name, $this->attributes))
        {
            $this->attributes[$name] = $value;
        }
        else
        {
            $this->set_metadata($name, $value);
        }
        $this->dirty = true;
    }

    public function get_metadata($name)
    {
        $md = $this->get_metadata_object($name);

        if ($md)
        {
            return $md->value;
        }
        return null;
    }

    protected function get_metadata_object($name)
    {
        if (isset($this->metadata_cache[$name]))
        {
            return $this->metadata_cache[$name];
        }

        $md = null;

        if ((int) ($this->guid) > 0)
        {
            $md = get_metadata_byname($this->guid, $name);
        }

        if (!$md)
        {
            $md = new EntityMetadata();
            $md->entity_guid = $this->guid;
            $md->name = $name;
            $md->value = null;
            $md->owner_guid = $this->owner_guid;
        }

        $this->metadata_cache[$name] = $md;
        return $md;
    }

    public function set_metadata($name, $value)
    {
        $md = $this->get_metadata_object($name);
        $md->value = $value;
        return true;
    }

    public function clear_metadata()
    {
        return Database::delete("DELETE from metadata where entity_guid=?", array($this->guid));
    }
    
    public function get_sub_entities()
    {
        $guid = $this->guid;
        return array_map('entity_row_to_entity',
            Database::get_rows("SELECT * from entities WHERE container_guid=? or owner_guid=?", array($guid, $guid))
        );
    }
    
    function can_edit()
    {
        return $this->can_user_edit(Session::get_loggedin_user());
    }
    /**
     * Determines whether or not the specified user (can edit the entity
     *
     * @param int $user The user
     * @return true|false
     */
    function can_user_edit($user)
    {
        if (!is_null($user))
        {
            if (($this->owner_guid == $user->guid)
             || ($this->container_guid == $user->guid)
             || ($this->guid == $user->guid)
             || $user->admin)
            {
                return true;
            }

            $container_entity = get_entity($this->container_guid);

            if ($container_entity && $container_entity->can_edit())
                return true;
        }
        return false;
    }   

    /**
     * Returns the actual entity of the user who owns this entity, if any
     *
     * @return Entity The owning user
     */
    public function get_owner_entity() { return get_entity($this->get('owner_guid')); }
    
    public function get_title()
    {
        return __("item:".strtolower(get_class($this)));
    }

    public function get_language()
    {
        $language = @$this->attributes['language'];
        if ($language)
        {
            return $language;
        }
        $container = $this->get_container_entity();
        if ($container)
        {
            return $container->get_language();
        }
        else
        {
            return 'en';
        }
    }

    /**
     * Gets the display URL for this entity
     *
     * @return string The URL
     */
    public function get_url() {
        return null;
    }

    /**
     * Return a url for the entity's icon, trying multiple alternatives.
     *
     * @param string $size Either 'large','medium','small' or 'tiny'
     * @return string The url or false if no url could be worked out.
     */
    public function get_icon($size = 'medium')
    {
        return Config::get('url')."_graphics/default{$size}.gif";
    }

    /**
     * Save generic attributes to the entities table.
     */
    public function save()
    {
        $time = time();
        $this->time_updated = $time;

        if (!$this->time_created)
        {
            $this->time_created = $time;
        }        
        
        if ($this->container_guid == 0)
        {
            $this->container_guid = $this->owner_guid;
        }
        
        $entity_values = array(
            'owner_guid' => $this->owner_guid,
            'container_guid' => $this->container_guid,
            'status' => $this->status,
            'time_updated' => $this->time_updated,
            'time_created' => $this->time_created,
            'subtype' => $this->subtype,
        );
        
        $guid = $this->guid;
        
        if ($guid > 0)
        {
            Database::update_row('entities', 'guid', $guid, $entity_values);
        }
        else
        {            
            $this->guid = Database::insert_row('entities', $entity_values);
            if (!$this->guid)
                throw new IOException(__('error:BaseEntitySaveFailed'));
        }        
        $this->save_metadata();        
        $this->save_table_attributes();
        
        $this->clear_from_cache();
        $this->cache_for_current_request();
        
        trigger_event('update',get_class($this),$this);
    }

    function save_metadata()
    {
        foreach($this->metadata_cache as $name => $md)
        {
            if ($md->dirty)
            {
                if ($md->value === null)
                {
                    $md->delete();
                }
                else
                {
                    $md->entity_guid = $this->guid;
                    $md->save();
                }                
            }
        }
    }

    public function set_status($status)
    {
        $this->status = $status;
    }
    
    /**
     * Disable this entity.
     */
    public function disable()
    {
        $this->set_status(EntityStatus::Disabled);
    }

    /**
     * Re-enable this entity.
     */
    public function enable()
    {
        $this->set_status(EntityStatus::Enabled);
    }

    /**
     * Is this entity enabled?
     *
     * @return boolean
     */
    public function is_enabled()
    {
        return $this->status == EntityStatus::Enabled;
    }

    function delete_recursive()
    {
        if ($recursive)
        {
            $sub_entities = $this->get_sub_entities();
            if ($sub_entities)
            {
                foreach ($sub_entities as $e)
                    $e->delete_recursive();
            }
        }    
        
        $this->delete();
    }
    
    /**
     * Delete this entity.
     */
    public function delete()
    {
        $this->clear_metadata();

        $res = Database::delete("DELETE from entities where guid=?", array($this->guid));
                
        parent::delete();
        $this->clear_from_cache();
        
        trigger_event('delete',get_class($this),$this);
    }

    function get_container_entity()
    {
        return get_entity($this->container_guid);
    }

    function get_root_container_entity()
    {
        if ($this->container_guid)
        {
            $containerEntity = $this->get_container_entity();
            if ($containerEntity == null || $containerEntity->guid == $this->guid)
            {
                return $this;
            }
            else
            {
                return $containerEntity->get_root_container_entity();
            }
        }
        else
        {
            return $this;
        }
    }
        
    public function translate_field($field, $isHTML = false, $viewLang = null)
    {
        $text = trim($this->$field);
        if (!$text)
        {
            return '';
        }

        $origLang = $this->get_language();
        if ($viewLang == null)
        {
            $viewLang = Language::get_current_code();
        }
                
        if ($origLang != $viewLang)
        {            
            $translateMode = get_translate_mode();
            $translation = $this->lookup_translation($field, $origLang, $viewLang, $translateMode, $isHTML);
            
            trigger_event('translate',get_class($this), $translation);
            
            if ($translation->owner_guid)
            {
                $viewTranslation = ($translateMode > TranslateMode::None);
            }
            else
            {
                $viewTranslation = ($translateMode == TranslateMode::All);
            }

            if ($viewTranslation && $translation->id)
            {
                return $translation->value;
            }
            else
            {
                return $this->$field;
            }
        }

        return $text;
    }        
        
    function lookup_auto_translation($prop, $origLang, $viewLang, $isHTML)
    {        
        $guid = $this->guid;
    
        $autoTrans =  Translation::query()
            ->where('property=?', $prop)
            ->where('lang=?',$viewLang)
            ->where('container_guid=?',$guid)
            ->where('html=?', $isHTML ? 1 : 0)
            ->where('owner_guid = 0')
            ->get();             
    
        if ($autoTrans && !$autoTrans->is_stale())
        {        
            return $autoTrans;
        }
        else
        {
            $text = GoogleTranslate::get_auto_translation($this->$prop, $origLang, $viewLang);

            if ($text != null)
            {
                if (!$autoTrans)
                {
                    $autoTrans = new Translation();                    
                    $autoTrans->owner_guid = 0;
                    $autoTrans->container_guid = $this->guid;
                    $autoTrans->property = $prop;
                    $autoTrans->html = $isHTML;
                    $autoTrans->lang = $viewLang;
                }
                $autoTrans->value = $text;                
                $autoTrans->save();
                
                return $autoTrans;
            }
        }
    }

    function lookup_translation($prop, $origLang, $viewLang, $translateMode = TranslateMode::ManualOnly, $isHTML = false)
    {
        $guid = $this->guid;
        
        $humanTrans = Translation::query()
            ->where('property=?', $prop)
            ->where('lang=?',$viewLang)
            ->where('container_guid=?',$guid)
            ->where('html=?', $isHTML ? 1 : 0)
            ->where('owner_guid > 0')
            ->order_by('time_updated desc')
            ->get();                

        $doAutoTranslate = ($translateMode == TranslateMode::All);

        if ($doAutoTranslate && (!$humanTrans || $humanTrans->is_stale()))
        {
            $autoTrans = $this->lookup_auto_translation($prop, $origLang, $viewLang, $isHTML);
            if ($autoTrans)
            {
                return $autoTrans;
            }
        }
        
        if ($humanTrans)
        {
            return $humanTrans;            
        }
        else
        {        
            // return translation with empty value
            $tempTrans = new Translation();
            $tempTrans->owner_guid = 0;
            $tempTrans->container_guid = $this->guid;
            $tempTrans->property = $prop;
            $tempTrans->lang = $viewLang;
            $tempTrans->html = $isHTML;        
            return $tempTrans;
        }
    }    

    public function set_content($content, $isHTML)
    {
        if ($isHTML)
        {
            $content = Markup::sanitize_html($content);
        }
        else
        {
            $content = view('output/longtext', array('value' => $content));
        }
        
        $this->content = $content;
        $this->set_data_type(DataType::HTML, true);

        if ($isHTML)
        {
            $thumbnailUrl = UploadedFile::get_thumbnail_url_from_html($content);

            $this->set_data_type(DataType::Image, $thumbnailUrl != null);
            $this->thumbnail_url = $thumbnailUrl;            
        }

        if (!$this->language)
        {            
            $this->language = GoogleTranslate::guess_language($this->content);
        }
    }

    public function render_content($markup_mode = null)
    {
        $isHTML = $this->has_data_type(DataType::HTML);

        $content = $this->translate_field('content', $isHTML);

        if ($isHTML)
        {
            $content = Markup::render_custom_tags($content, $markup_mode);
        
            return $content; // html content should be sanitized when it is input!
        }
        else
        {
            return view('output/longtext', array('value' => $content));
        }
    }

    public function has_data_type($dataType)
    {
        return ($this->data_types & $dataType) != 0;
    }

    public function set_data_type($dataType, $val)
    {
        if ($val)
        {
            $this->data_types |= $dataType;
        }
        else
        {
            $this->data_types &= ~$dataType;
        }
    }        
    
    static function query()
    {
        return new Query_SelectEntity(static::$table_name);
    }
    
    // Loggable interface
    public function get_id() { return $this->guid; }
    public function get_class_name() { return get_class($this); }
    static function get_object_from_id($id) { return get_entity($id); }    
}

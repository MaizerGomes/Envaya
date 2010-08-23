<?php

abstract class Entity implements Loggable, Serializable
{
    /**
     * The main attributes of an entity.
     * Blank entries for all database fields should be created by the constructor.
     * Subclasses should add to this in their constructors.
     * Any field not appearing in this will be viewed as metadata
     */
    protected $attributes = array();    
    protected $metadata_cache = array();        

    static $table_name;
    static $table_attributes;
    
    static $subtype_id = 0;

    function __construct($row = null)
    {
        $this->initialize_attributes();

        if ($row)
        {
            if (!$this->load_from_partial_table_row($row))
            {
                throw new IOException(sprintf(__('error:FailedToLoadGUID'), get_class(), $row->guid));
            }
        }
    }

    public function serialize()
    {
        return serialize($this->attributes);
    }

    public function unserialize($data)
    {
        $this->initialize_attributes();
        $this->attributes = unserialize($data);
    }

    protected function load_from_partial_table_row($row)
    {
        $entityRow = (property_exists($row, 'type')) ? $row : get_entity_as_row($row->guid);
        if (!$this->load_from_table_row($entityRow))
        {
            return false;
        }
            
        if (!property_exists($row, get_first_key(static::$table_attributes)))
        {
            $objectEntityRow = $this->select_table_attributes($row->guid);
            return $this->load_from_table_row($objectEntityRow);             
        }
        return true;
    }

    /**
     * Initialise the attributes array.
     * This is vital to distinguish between metadata and base parameters.
     *
     * Place your base parameters here.
     *
     * @return void
     */
    protected function initialize_attributes()
    {
        $this->attributes['guid'] = "";        
        $this->attributes['type'] = "object";
        $this->attributes['subtype'] = static::$subtype_id;
        $this->attributes['owner_guid'] = 0;
        $this->attributes['container_guid'] = 0;
        $this->attributes['site_guid'] = 0;
        $this->attributes['time_created'] = "";
        $this->attributes['time_updated'] = "";
        $this->attributes['enabled'] = "yes";
        
        foreach (static::$table_attributes as $name => $default)
        {
            $this->attributes[$name] = $default;
        }
    }

    protected function get_table_attributes()
    {
        $tableAttributes = array();
        foreach (static::$table_attributes as $name => $default)
        {
            $tableAttributes[$name] = $this->attributes[$name];
        }
        return $tableAttributes;
    }

    public function save_table_attributes()
    {
        $tableName = static::$table_name;
    
        $guid = $this->guid;
        if (get_data_row("SELECT guid from $tableName where guid = ?", array($guid)))
        {
            $args = array();
            $set = array();
            foreach ($this->get_table_attributes() as $name => $value)
            {
                $set[] = "`$name` = ?";
                $args[] = $value;
            }

            $args[] = $guid;

            return update_data("UPDATE $tableName set ".implode(',', $set)." where guid = ?", $args);
        }
        else
        {
            $columns = array('guid');
            $questions = array('?');
            $args = array($guid);

            foreach ($this->get_table_attributes() as $name => $value)
            {
                $columns[] = "`$name`";
                $questions[] = '?';
                $args[] = $value;
            }

            return update_data("INSERT into $tableName (".implode(',', $columns).") values (".implode(',', $questions).")", $args);
        }
    }

    public function delete_table_attributes()
    {
        $tableName = static::$table_name;
        delete_data("DELETE from $tableName where guid=?", array($this->guid));
        return true;
    }

    public function select_table_attributes($guid)
    {
        $tableName = static::$table_name;
        return get_data_row("SELECT * from $tableName where guid=?", array($guid));
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

        // No, so see if its in the meta data for this entity
        $meta = $this->get_metadata($name);
        if ($meta)
            return $meta;

        // Can't find it, so return null
        return null;
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
            // Check that we're not trying to change the guid!
            if ((array_key_exists('guid', $this->attributes)) && ($name=='guid'))
                return false;

            $this->attributes[$name] = $value;
        }
        else
        {
            return $this->set_metadata($name, $value);
        }

        return true;
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

    /**
     * Class member get overloading
     *
     * @param string $name
     * @return mixed
     */
    function __get($name) { return $this->get($name); }

    /**
     * Class member set overloading
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    function __set($name, $value) { return $this->set($name, $value); }

    /**
     * Supporting isset.
     *
     * @param string $name The name of the attribute or metadata.
     * @return bool
     */
    function __isset($name) { if ($this->$name!="") return true; else return false; }

    /**
     * Supporting unsetting of magic attributes.
     *
     * @param string $name The name of the attribute or metadata.
     */
    function __unset($name)
    {
        if (array_key_exists($name, $this->attributes))
        {
            $this->attributes[$name] = "";
        }
        else
        {
            $this->set_metadata($name, null);
        }
    }

    public function set_metadata($name, $value)
    {
        $md = $this->get_metadata_object($name);
        $md->value = $value;
        return true;
    }

    public function clear_metadata()
    {
        return delete_data("DELETE from metadata where entity_guid=?", array($this->guid));
    }
    
    public function get_sub_entities()
    {
        $guid = $this->guid;
        return array_map('entity_row_to_entity',
            get_data("SELECT * from entities WHERE container_guid=? or owner_guid=? or site_guid=?", array($guid, $guid, $guid))
        );
    }

    /**
     * Determines whether or not the specified user (by default the current one) can edit the entity
     *
     * @param int $user The user, optionally (defaults to the currently logged in user)
     * @return true|false
     */
    function can_edit($user = null)
    {
        if (!$user)
            $user = Session::get_loggedin_user();

        if (!is_null($user))
        {
            if (($this->owner_guid == $user->guid)
             || ($this->container_guid == $user->guid)
             || ($this->type == "user" && $this->guid == $user->guid)
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
        global $CONFIG;
        return "{$CONFIG->url}_graphics/default{$size}.gif";
    }

    /**
     * Save generic attributes to the entities table.
     */
    public function save()
    {
        $guid = (int) $this->guid;
        if ($guid > 0)
        {
            if (trigger_event('update',$this->type,$this))
            {
                $time = time();
                $this->time_updated = $time;

                $res = update_data("UPDATE entities set owner_guid=?, container_guid=?, enabled=?, time_updated=? WHERE guid=?",
                    array($this->owner_guid,$this->container_guid,$this->enabled,$this->time_updated,$guid)
                );
                cache_entity($this);
            }
        }
        else
        {
            $time = time();

            if ($this->container_guid == 0)
                $this->container_guid = $this->owner_guid;

            if ($this->type == "")
                throw new InvalidParameterException(__('error:EntityTypeNotSet'));

            $this->time_created = $time;
            $this->time_updated = $time;

            $this->attributes['guid'] = insert_data("INSERT into entities (type, subtype, owner_guid, site_guid, container_guid, enabled,  time_created, time_updated) values (?,?,?,?,?,?,?,?)",
                array($this->type, $this->subtype, $this->owner_guid, $this->site_guid,
                    $this->container_guid, $this->enabled, $this->time_created, $this->time_updated)
            );

            if (!$this->guid)
                throw new IOException(__('error:BaseEntitySaveFailed'));

            if ($this->guid)
                cache_entity($this);

            $res = true;
        }

        $this->save_metadata();        

        return $res && $this->save_table_attributes();
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

    protected function load_from_table_row($row)
    {
        $typeBefore = $this->attributes['type'];

        $objarray = (array) $row;

        foreach($objarray as $key => $value)
            $this->attributes[$key] = $value;

        if ($this->attributes['type'] != $typeBefore)
            throw new InvalidClassException(sprintf(__('error:NotValidEntity'), $guid, get_class()));

        global $ENTITY_CACHE;
        $ENTITY_CACHE[$this->guid] = $this;

        return true;
    }

    /**
     * Disable this entity.
     */
    public function disable()
    {
        $this->enabled = 'no';
    }

    /**
     * Re-enable this entity.
     */
    public function enable()
    {
        $this->enabled = 'yes';
    }

    /**
     * Is this entity enabled?
     *
     * @return boolean
     */
    public function is_enabled()
    {
        return ($this->enabled == 'yes');
    }

    /**
     * Delete this entity.
     */
    public function delete()
    {
        if (trigger_event('delete',$this->type,$this))
        {
            $sub_entities = $this->get_sub_entities();
            if ($sub_entities)
            {
                foreach ($sub_entities as $e)
                    $e->delete();
            }

            $this->clear_metadata();

            $res = delete_data("DELETE from entities where guid=?", array($this->guid));

            invalidate_cache_for_entity($this->guid);

            return $res && $this->delete_table_attributes();
        }
        return false;
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

    function get_default_view_name()
    {
        $view_name = "object/".strtolower(get_class($this));
        if (view_exists($view_name)) 
        {
            return $view_name;
        }
        else
        {
            return 'object/default';
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
            $viewLang = get_language();
        }
                
        if ($origLang != $viewLang)
        {            
            $translateMode = get_translate_mode();
            $translation = $this->lookup_translation($field, $origLang, $viewLang, $translateMode, $isHTML);
            
            trigger_event('translate',$this->type, $translation);
            
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
        $autoTrans = Translation::query()
            ->where('property=?', $prop)
            ->where('lang=?',$viewLang)
            ->where('container_guid=?',$this->guid)
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
        $humanTrans = Translation::query()
            ->where('property=?', $prop)
            ->where('lang=?',$viewLang)
            ->where('container_guid=?',$this->guid)
            ->where('html=?', $isHTML ? 1 : 0)
            ->where('owner_guid > 0')
            ->order_by('time_updated desc')
            ->get(); 

        $doAutoTranslate = ($translateMode == TranslateMode::All);

        if ($doAutoTranslate && (!$humanTrans || $humanTrans->is_stale()))
        {
            return $this->lookup_auto_translation($prop, $origLang, $viewLang, $isHTML);
        }
        else if ($humanTrans)
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
            $thumbnailUrl = get_thumbnail_src($content);

            if ($thumbnailUrl != null)
            {
                $this->set_data_type(DataType::Image, $thumbnailUrl != null);
                $this->thumbnail_url = $thumbnailUrl;
            }
        }

        if (!$this->language)
        {
            $this->language = GoogleTranslate::guess_language($this->content);
        }
    }

    public function render_content()
    {
        $isHTML = $this->has_data_type(DataType::HTML);

        $content = $this->translate_field('content', $isHTML);

        if ($isHTML)
        {
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
        $query = new Query_SelectEntity(static::$table_name);
        $query->where("type='object'");
        $query->where("subtype=?", static::$subtype_id);
        return $query;
    }

    static function query_by_metadata($meta_name, $meta_value = "")
    {
        $query = static::query();  
        $query->join('JOIN metadata m on e.guid = m.entity_guid');

        if ($meta_name!=="")
        {
            $query->where("m.name=?", $meta_name);
        }

        if ($meta_value!=="")
        {
            $query->where("m.value=?", $meta_value);
        }
        return $query;
    }
    
    // Loggable interface
    public function get_id() { return $this->guid; }
    public function get_class_name() { return get_class($this); }
    static function get_object_from_id($id) { return get_entity($id); }    
}

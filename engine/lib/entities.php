<?php

    class EntityStatus
    {
        const Disabled = 0; // aka deleted
        const Enabled = 1;
        const Draft = 2;
    }

    class EntityRegistry
    {
        private static $subtype_to_class = array(
            0 => 'User',
            1 => 'UploadedFile',
            3 => 'Widget',
            4 => 'Organization',
            7 => 'NewsUpdate',        
            12 => 'FeaturedSite',
            13 => 'EmailTemplate',
            16 => 'Comment',
            17 => 'FeaturedPhoto',
            19 => 'OrgRelationship',            
            21 => 'DiscussionMessage',
            22 => 'DiscussionTopic',
        );
        private static $class_to_subtype = null;
        
        static function register_subtype($subtype_id, $class_name)
        {
            static::$subtype_to_class[$subtype_id] = $class_name;
        }
        
        static function get_subtype_class($subtype_id)
        {
            return @static::$subtype_to_class[$subtype_id];
        }
        
        static function get_subtype_id($class_name)
        {
            if (static::$class_to_subtype == null)
            {
                static::$class_to_subtype = array_flip(static::$subtype_to_class);
            }
            return @static::$class_to_subtype[$class_name];
        }
    }

    /**
     * Retrieve the entity details for a specific GUID, returning it as a stdClass db row.
     *
     * @param int $guid The GUID of the object to extract
     */
    function get_entity_as_row($guid)
    {
        if (!$guid)
            return false;

        return Database::get_row("SELECT * from entities where guid=?", array($guid));
    }
    
    function entity_row_to_entity($row)
    {
        if (!$row)
            return null;

        $classname = EntityRegistry::get_subtype_class($row->subtype);

        if ($classname && class_exists($classname))
        {   
            return new $classname($row);
        }
        else
        {
            throw new ClassException(sprintf(__('error:ClassnameNotClass'), ($classname ?: "Entity subtype {$row->subtype}"), 'Entity'));
        }
    }
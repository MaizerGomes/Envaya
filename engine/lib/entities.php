<?php
	/**
	 * Elgg entities.
	 * Functions to manage all elgg entities (sites, collections, objects and users).
	 * 
	 * @package Elgg
	 * @subpackage Core

	 * @author Curverider Ltd <info@elgg.com>

	 * @link http://elgg.org/
	 */

	/// Cache objects in order to minimise database access.
	$ENTITY_CACHE = NULL;
	
	/// Cache subtype searches
	$SUBTYPE_CACHE = NULL;
	
	/// Require the locatable interface TODO: Move this into start.php?
	require_once('location.php');
	
	/**
	 * ElggEntity The elgg entity superclass
	 * This class holds methods for accessing the main entities table.
	 * 
	 * @author Curverider Ltd <info@elgg.com>
	 * @package Elgg
	 * @subpackage Core
	 */
	abstract class ElggEntity implements 
		Loggable,	// Can events related to this object class be logged
		Iterator,	// Override foreach behaviour
		ArrayAccess // Override for array access
	{
		/** 
		 * The main attributes of an entity.
		 * Blank entries for all database fields should be created by the constructor.
		 * Subclasses should add to this in their constructors.
		 * Any field not appearing in this will be viewed as a 
		 */
		protected $attributes;

		/**
		 * If set, overrides the value of getURL()
		 */
		protected $url_override;
		
		/**
		 * Icon override, overrides the value of getIcon().
		 */
		protected $icon_override;
				
        protected $metadata_cache;
				
        protected $table_attribute_names;                
		
		/**
		 * Initialise the attributes array. 
		 * This is vital to distinguish between metadata and base parameters.
		 * 
		 * Place your base parameters here.
		 * 
		 * @return void
		 */
		protected function initialise_attributes()
		{
			initialise_entity_cache();

			// Create attributes array if not already created
			if (!is_array($this->attributes)) $this->attributes = array();
            if (!is_array($this->metadata_cache)) $this->metadata_cache = array();
			
			$this->attributes['guid'] = "";
			$this->attributes['type'] = "";
			$this->attributes['subtype'] = 0;
			
			$this->attributes['owner_guid'] = get_loggedin_userid();
			$this->attributes['container_guid'] = get_loggedin_userid();
			
			$this->attributes['site_guid'] = 0;
			$this->attributes['access_id'] = ACCESS_PRIVATE;
			$this->attributes['time_created'] = "";
			$this->attributes['time_updated'] = "";
			$this->attributes['enabled'] = "yes";		
		}
				
        
        protected function initializeTableAttributes($tableName, $arr)
        {
            $tableAttributes = array();
            foreach ($arr as $name => $default)
            {
                $tableAttributes[] = $name;
                $this->attributes[$name] = $default;
            }            
            
            if (!is_array($this->table_attribute_names))
            {
                $this->table_attribute_names = array();
            }
            
            $this->table_attribute_names[$tableName] = $tableAttributes;
        }
                
        protected function getTableAttributes($tableName)
        {
            $tableAttributes = array();
            foreach ($this->table_attribute_names[$tableName] as $name)
            {
                $tableAttributes[$name] = $this->attributes[$name];
            }
            return $tableAttributes;
        }                

        public function saveTableAttributes($tableName)
        {
            $guid = $this->guid;
            if (get_data_row_2("SELECT guid from $tableName where guid = ?", array($guid)))
            {
                $args = array();
                $set = array();
                foreach ($this->getTableAttributes($tableName) as $name => $value)
                {
                    $set[] = "$name = ?";
                    $args[] = $value;
                }
                
                $args[] = $guid;
            
                return update_data_2("UPDATE $tableName set ".implode(',', $set)." where guid = ?", $args);
            }
            else
            {
                $columns = array('guid');
                $questions = array('?');
                $args = array($guid);
                
                foreach ($this->getTableAttributes($tableName) as $name => $value)
                {
                    $columns[] = $name;
                    $questions[] = '?';
                    $args[] = $value;
                }
                                     
                return update_data_2("INSERT into $tableName (".implode(',', $columns).") values (".implode(',', $questions).")", $args);                
            }        
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
			// See if its in our base attribute
			if (isset($this->attributes[$name])) {
				return $this->attributes[$name];
			}
			
			// No, so see if its in the meta data for this entity
			$meta = $this->getMetaData($name);
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
		 * not in their gets set as METADATA.
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
				return $this->setMetaData($name, $value);
		
			return true;
		}
			
		/**
		 * Get a given piece of metadata.
		 * 
		 * @param string $name
		 */
		public function getMetaData($name)
		{
            $md = $this->getMetaDataObject($name);
            
			if ($md) 
            {
				return $md->value;
			} 				
			return null;
		}
		
        protected function getMetaDataObject($name)
        {
            if (isset($this->metadata_cache[$name]))
            {
                return $this->metadata_cache[$name];        
            }    
            
            $md = null;
            
            if ((int) ($this->guid) > 0) 
            {
                $md = get_metadata_byname($this->getGUID(), $name);                
            } 
            
            if (!$md)
            {
                $md = new ElggMetadata();
                $md->entity_guid = $this->guid;
                $md->name = $name;
                $md->value = null;
                $md->owner_guid = $this->owner_guid;
                $md->access_id = $this->access_id;                
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
				$this->setMetaData($name, null);
            }    
		}
		
		/**
		 * Set a piece of metadata.
		 * 
		 * @param string $name
		 * @param mixed $value
		 * @param string $value_type
		 * @param bool $multiple
		 * @return bool
		 */
		public function setMetaData($name, $value, $value_type = "", $multiple = false)
		{        
            $md = $this->getMetaDataObject($name);            
            $md->value = $value;
            $md->dirty = true;
            return true;
		}
		
		/**
		 * Clear metadata.
		 */
		public function clearMetaData($name = "")
		{
			if (empty($name)) {
				return clear_metadata($this->getGUID());
			} else {
				return remove_metadata($this->getGUID(),$name);
			}
		}
		
        public function getSubEntities()
        {
            $guid = $this->guid;
            return array_map('entity_row_to_elggstar', 
                get_data_2("SELECT * from entities WHERE container_guid=? or owner_guid=? or site_guid=?", array($guid, $guid, $guid))
            );
        }
        
		/**
		 * Remove all entities associated with this entity
		 *
		 * @return true
		 */
		public function clearRelationships() {
			remove_entity_relationships($this->getGUID());
			remove_entity_relationships($this->getGUID(),"",true);
			return true;
		}
		
		/**
		 * Add a relationship.
		 *
		 * @param int $guid Relationship to link to.
		 * @param string $relationship The type of relationship.
		 */
		public function addRelationship($guid, $relationship)
		{
			return add_entity_relationship($this->getGUID(), $relationship, $guid);
		}
		
		function setPrivateSetting($name, $value) {
			return set_private_setting($this->getGUID(), $name, $value);
		}
		
		function getPrivateSetting($name) {
			return get_private_setting($this->getGUID(), $name);
		}
		
		function removePrivateSetting($name) {
			return remove_private_setting($this->getGUID(), $name);
		}
		
		/**
		 * Gets an array of entities from a specific relationship type
		 *
		 * @param string $relationship Relationship type (eg "friends")
		 * @param true|false $inverse Is this an inverse relationship?
		 * @param int $limit Number of elements to return
		 * @param int $offset Indexing offset
		 * @return array|false An array of entities or false on failure
		 */		
		function getEntitiesFromRelationship($relationship, $inverse = false, $limit = 50, $offset = 0) {
			return get_entities_from_relationship($relationship,$this->getGUID(),$inverse,"","","","time_created desc",$limit,$offset);			
		}
		
		/**
		 * Gets the number of of entities from a specific relationship type
		 *
		 * @param string $relationship Relationship type (eg "friends")
		 * @return int|false The number of entities or false on failure
		 */		
		function countEntitiesFromRelationship($relationship) {
			return get_entities_from_relationship($relationship,$this->getGUID(),false,"","","","time_created desc",null,null,true);			
		}
		
		/**
		 * Determines whether or not the specified user (by default the current one) can edit the entity 
		 *
		 * @param int $user_guid The user GUID, optionally (defaults to the currently logged in user)
		 * @return true|false
		 */
		function canEdit($user_guid = 0) {
			return can_edit_entity($this->getGUID(),$user_guid);
		}
		
		/**
		 * Determines whether or not the specified user (by default the current one) can edit metadata on the entity 
		 *
		 * @param ElggMetadata $metadata The piece of metadata to specifically check
		 * @param int $user_guid The user GUID, optionally (defaults to the currently logged in user)
		 * @return true|false
		 */
		function canEditMetadata($metadata = null, $user_guid = 0) {
			return can_edit_entity_metadata($this->getGUID(), $user_guid, $metadata);
		}
		
		/**
		 * Returns whether the given user (or current user) has the ability to write to this group.
		 *
		 * @param int $user_guid The user.
		 * @return bool
		 */
		public function canWriteToContainer($user_guid = 0)
		{
			return can_write_to_container($user_guid, $this->getGUID());
		}
		
		/**
		 * Obtain this entity's access ID
		 *
		 * @return int The access ID
		 */
		public function getAccessID() { return $this->get('access_id'); }
		
		/**
		 * Obtain this entity's GUID
		 *
		 * @return int GUID
		 */
		public function getGUID() { return $this->get('guid'); }
		
		/**
		 * Get the owner of this entity
		 *
		 * @return int The owner GUID
		 */
		public function getOwner() { return $this->get('owner_guid'); }
		
		/**
		 * Returns the actual entity of the user who owns this entity, if any
		 *
		 * @return ElggEntity The owning user
		 */
		public function getOwnerEntity() { return get_entity($this->get('owner_guid')); }
		
		/**
		 * Gets the type of entity this is
		 *
		 * @return string Entity type
		 */
		public function getType() { return $this->get('type'); }
		
		/**
		 * Returns the subtype of this entity
		 *
		 * @return string The entity subtype
		 */
		public function getSubtype() {
			return $this->get('subtype');
		}
		
        public function getSubtypeName() 
        {        
            return get_subtype_from_id($this->get('subtype'));
        }

        
        
		/**
		 * Gets the UNIX epoch time that this entity was created
		 *
		 * @return int UNIX epoch time
		 */
		public function getTimeCreated() { return $this->get('time_created'); }
		
		/**
		 * Gets the UNIX epoch time that this entity was last updated
		 *
		 * @return int UNIX epoch time
		 */
		public function getTimeUpdated() { return $this->get('time_updated'); }
		
		/**
		 * Gets the display URL for this entity
		 *
		 * @return string The URL
		 */
		public function getURL() {
			if (!empty($this->url_override)) 
                return $this->url_override;
			
            global $CONFIG;

            $url = "";
            $entity = $this;
            
            if (isset($CONFIG->entity_url_handler[$entity->getType()][$entity->getSubTypeName()])) 
            {
                $function = $CONFIG->entity_url_handler[$entity->getType()][$entity->getSubTypeName()];
                if (is_callable($function)) 
                {
                    $url = $function($entity);
                }
            } 
            elseif (isset($CONFIG->entity_url_handler[$entity->getType()]['all'])) 
            {
                $function =  $CONFIG->entity_url_handler[$entity->getType()]['all'];
                if (is_callable($function)) {
                    $url = $function($entity);
                }
            } 
            elseif (isset($CONFIG->entity_url_handler['all']['all'])) 
            {
                $function =  $CONFIG->entity_url_handler['all']['all'];
                if (is_callable($function)) {
                    $url = $function($entity);
                }
            }

            if ($url == "") 
            {
                $url = $CONFIG->url . "pg/view/" . $entity_guid;
            }
            return $url;
            
		}
		
		/**
		 * Overrides the URL returned by getURL
		 *
		 * @param string $url The new item URL
		 * @return string The URL
		 */
		public function setURL($url) {
			$this->url_override = $url;
			return $url;
		}
		
		/**
		 * Return a url for the entity's icon, trying multiple alternatives.
		 *
		 * @param string $size Either 'large','medium','small' or 'tiny'
		 * @return string The url or false if no url could be worked out.
		 */
		public function getIcon($size = 'medium')
		{
			if (isset($this->icon_override[$size])) return $this->icon_override[$size];
			return get_entity_icon_url($this, $size);
		}
		
		/**
		 * Set an icon override for an icon and size.
		 *
		 * @param string $url The url of the icon.
		 * @param string $size The size its for.
		 * @return bool
		 */
		public function setIcon($url, $size = 'medium')
		{
			if (!$this->icon_override) 
                $this->icon_override = array();
                
			$this->icon_override[$size] = $url;
			
			return true;
		}
				
		/**
		 * Save generic attributes to the entities table.
		 */
		public function save()
		{
			$guid = (int) $this->guid;
			if ($guid > 0)
			{ 
				cache_entity($this);

				$res = update_entity(
					$this->get('guid'),
					$this->get('owner_guid'),
					$this->get('access_id'),
					$this->get('container_guid')
				);
			}
			else
			{ 
				$this->attributes['guid'] = create_entity($this->attributes['type'], $this->attributes['subtype'], $this->attributes['owner_guid'], $this->attributes['access_id'], $this->attributes['site_guid'], $this->attributes['container_guid']); // Create a new entity (nb: using attribute array directly 'cos set function does something special!)
				if (!$this->attributes['guid']) throw new IOException(elgg_echo('IOException:BaseEntitySaveFailed'));                
                
                // Cache object handle
                if ($this->attributes['guid']) cache_entity($this); 
                
                $res = $this->attributes['guid'];            
                
			}
            
            $this->saveMetaData();

            return $res;
		}
		
        function saveMetaData()
        {
            foreach($this->metadata_cache as $name => $md) 
            {
                if ($md->dirty)
                {
                    $md->entity_guid = $this->guid;
                    $md->save();
                    $md->dirty = false;
                }
            }   
                    
        }
        
		/**
		 * Load the basic entity information and populate base attributes array.
		 * 
		 * @param int $guid 
		 */
		protected function load($guid)
		{		
			$row = get_entity_as_row($guid); 
			
			if ($row)
			{
                $this->loadFromTableRow($row);
				return true;
			}
			
			return false;
		}
		
        protected function loadFromTableRow($row)
        {
            $objarray = (array) $row;
            
            foreach($objarray as $key => $value) 
                $this->attributes[$key] = $value;
            
            if ($this->attributes['guid'])
                cache_entity($this); 
                        
            return true;
        }        
        
		/**
		 * Disable this entity.
		 * 
		 * @param string $reason Optional reason
		 * @param bool $recursive Recursively disable all contained entities?
		 */
		public function disable($reason = "", $recursive = true)
		{
			return disable_entity($this->get('guid'), $reason, $recursive);
		}
		
		/**
		 * Re-enable this entity.
		 */
		public function enable()
		{
			return enable_entity($this->get('guid'));
		}
		
		/**
		 * Is this entity enabled?
		 *
		 * @return boolean
		 */
		public function isEnabled()
		{
			if ($this->enabled == 'yes')
				return true;
				
			return false;
		}
		
		/**
		 * Delete this entity.
		 */
		public function delete() 
		{ 
			return delete_entity($this->get('guid'));
		}			
		
        function getRootContainerEntity()
        {
            if ($this->container_guid)
            {
                $containerEntity = $this->getContainerEntity();
                if ($containerEntity == null || $containerEntity->guid == $this->guid)
                {
                    return $this;
                }
                else
                {
                    return $containerEntity->getRootContainerEntity();
                }
            }
            else
            {
                return $this;
            }
        }    
                        
		// SYSTEM LOG INTERFACE ////////////////////////////////////////////////////////////
		
		/**
		 * Return an identification for the object for storage in the system log. 
		 * This id must be an integer.
		 * 
		 * @return int 
		 */
		public function getSystemLogID() { return $this->getGUID();	}
		
		/**
		 * Return the class name of the object.
		 */
		public function getClassName() { return get_class($this); }
		
		/**
		 * For a given ID, return the object associated with it.
		 * This is used by the river functionality primarily.
		 * This is useful for checking access permissions etc on objects.
		 */
		public function getObjectFromID($id) { return get_entity($id); }
		
		/**
		 * Return the GUID of the owner of this object.
		 */
		public function getObjectOwnerGUID() { return $this->owner_guid; }

		// ITERATOR INTERFACE //////////////////////////////////////////////////////////////
		/*
		 * This lets an entity's attributes be displayed using foreach as a normal array.
		 * Example: http://www.sitepoint.com/print/php5-standard-library
		 */
		
		private $valid = FALSE; 
		
   		function rewind() 
   		{ 
   			$this->valid = (FALSE !== reset($this->attributes));  
   		}
   
   		function current() 
   		{ 
   			return current($this->attributes); 
   		}
		
   		function key() 
   		{ 
   			return key($this->attributes); 
   		}
		
   		function next() 
   		{
   			$this->valid = (FALSE !== next($this->attributes));  
   		}
   		
   		function valid() 
   		{ 
   			return $this->valid;  
   		}
	
   		// ARRAY ACCESS INTERFACE //////////////////////////////////////////////////////////
		/*
		 * This lets an entity's attributes be accessed like an associative array.
		 * Example: http://www.sitepoint.com/print/php5-standard-library
		 */

		function offsetSet($key, $value)
		{
   			if ( array_key_exists($key, $this->attributes) ) {
     			$this->attributes[$key] = $value;
   			}
 		} 
 		
 		function offsetGet($key) 
 		{
   			if ( array_key_exists($key, $this->attributes) ) {
     			return $this->attributes[$key];
   			}
 		} 
 		
 		function offsetUnset($key) 
 		{
   			if ( array_key_exists($key, $this->attributes) ) {
     			$this->attributes[$key] = ""; // Full unsetting is dangerious for our objects
   			}
 		} 
 		
 		function offsetExists($offset) 
 		{
   			return array_key_exists($offset, $this->attributes);
 		} 
	}

	/**
	 * Initialise the entity cache.
	 */
	function initialise_entity_cache()
	{
		global $ENTITY_CACHE;
		
		if (!$ENTITY_CACHE)
			$ENTITY_CACHE = array(); //select_default_memcache('entity_cache'); // TODO: Replace with memcache?
	}
	
	/**
	 * Invalidate this class' entry in the cache.
	 * 
	 * @param int $guid The guid
	 */
	function invalidate_cache_for_entity($guid)
	{
		global $ENTITY_CACHE;
		
		$guid = (int)$guid;
			
		unset($ENTITY_CACHE[$guid]);
		//$ENTITY_CACHE->delete($guid);		
	}
	
	/**
	 * Cache an entity.
	 * 
	 * @param ElggEntity $entity Entity to cache
	 */
	function cache_entity(ElggEntity $entity)
	{
		global $ENTITY_CACHE;
		
		$ENTITY_CACHE[$entity->guid] = $entity;
	}
	
	/**
	 * Retrieve a entity from the cache.
	 * 
	 * @param int $guid The guid
	 */
	function retrieve_cached_entity($guid)
	{
		global $ENTITY_CACHE;
		
		$guid = (int)$guid;
			
		if (isset($ENTITY_CACHE[$guid])) 
			return $ENTITY_CACHE[$guid];
				
		return false;
	}
	
	/**
	 * As retrieve_cached_entity, but returns the result as a stdClass (compatible with load functions that
	 * expect a database row.)
	 * 
	 * @param int $guid The guid
	 */
	function retrieve_cached_entity_row($guid)
	{
		$obj = retrieve_cached_entity($guid);
		if ($obj)
		{
			$tmp = new stdClass;
			
			foreach ($obj as $k => $v)
				$tmp->$k = $v;
				
			return $tmp;
		}

		return false;
	}
	
	/**
	 * Return the integer ID for a given subtype, or false.
	 * 
	 * TODO: Move to a nicer place?
	 * 
	 * @param string $type
	 * @param string $subtype
	 */
	function get_subtype_id($type, $subtype)
	{
        global $CONFIG;
        foreach ($CONFIG->subtypes as $id => $info)
        {   
            if ($info[0] == $type && $info[1] == $subtype)
            {
                return $id;
            }
        }        

		return 0;
	}
	
	/**
	 * For a given subtype ID, return its identifier text.
	 *  
	 * TODO: Move to a nicer place?
	 * 
	 * @param int $subtype_id
	 */
	function get_subtype_from_id($subtype_id)
	{
        global $CONFIG;
        if (isset($CONFIG->subtypes[$subtype_id]))
        {
            return $CONFIG->subtypes[$subtype_id][1];
        }

		return false;
	}
	
	/**
	 * This function tests to see if a subtype has a registered class handler.
	 * 
	 * @param string $type The type
	 * @param string $subtype The subtype
	 * @return a class name or null
	 */
	function get_subtype_class($type, $subtype)
	{
        global $CONFIG;
		foreach ($CONFIG->subtypes as $id => $info)
        {
            if ($info[0] == $type && $info[1] == $subtype)
            {
                return $info[2];
            }
        }
		
		return NULL;
	}
	
	/**
	 * This function tests to see if a subtype has a registered class handler by its id.
	 * 
	 * @param int $subtype_id The subtype
	 * @return a class name or null
	 */
	function get_subtype_class_from_id($subtype_id)
	{
		global $CONFIG;        
        if (isset($CONFIG->subtypes[$subtype_id]))
        {
            return $CONFIG->subtypes[$subtype_id][2];
        }
        return NULL;
	}
	
	/**
	 * This function will register a new subtype, returning its ID as required.
	 * 
	 * @param string $type The type you're subtyping
	 * @param string $subtype The subtype label
	 * @param string $class Optional class handler (if you don't want it handled by the generic elgg handler for the type)
	 */
    function add_subtype($type, $subtype, $class = "")
	{
	}
	
	/**
	 * Update an existing entity.
	 *
	 * @param int $guid
	 * @param int $owner_guid
	 * @param int $access_id
	 * @param int $container_guid
	 */
	function update_entity($guid, $owner_guid, $access_id, $container_guid = null)
	{
		global $CONFIG, $ENTITY_CACHE;
		
		$guid = (int)$guid;
		$owner_guid = (int)$owner_guid;
		$access_id = (int)$access_id;
		$container_guid = (int) $container_guid;
		if (is_null($container_guid)) $container_guid = $owner_guid;
		$time = time();

		$entity = get_entity($guid);
		
        if (trigger_elgg_event('update',$entity->type,$entity)) {
            $ret = update_data_2("UPDATE entities set owner_guid=?, access_id=?, container_guid=?, time_updated=? WHERE guid=?", 
                array($owner_guid,$access_id,$container_guid,$time,$guid)
            );

            // If memcache is available then delete this entry from the cache
            static $newentity_cache;
            if ((!$newentity_cache) && (is_memcache_available())) 
                $newentity_cache = new ElggMemcache('new_entity_cache');
            if ($newentity_cache) $new_entity = $newentity_cache->delete($guid);

            // Handle cases where there was no error BUT no rows were updated!
            if ($ret===false)
                return false;

            return true;
        }
	}
	
	/**
	 * Determine whether a given user is able to write to a given container.
	 *
	 * @param int $user_guid The user guid, or 0 for get_loggedin_userid()
	 * @param int $container_guid The container, or 0 for the current page owner.
	 */
	function can_write_to_container($user_guid = 0, $container_guid = 0, $entity_type = 'all')
	{
		global $CONFIG;
	
		$user_guid = (int)$user_guid;
		$user = get_entity($user_guid);
		if (!$user) $user = get_loggedin_user();
		
		$container_guid = (int)$container_guid;
		if (!$container_guid) $container_guid = page_owner();
		if (!$container_guid) return true;

		$container = get_entity($container_guid);
		
		if ($container)
		{

			// If the user can edit the container, they can also write to it
			if ($container->canEdit($user_guid)) return true;
					
			// See if anyone else has anything to say
			return trigger_plugin_hook('container_permissions_check',$entity_type,array('container' => $container, 'user' => $user), false);
			
		}
		
		return false;
	}
	
	/**
	 * Create a new entity of a given type.
	 * 
	 * @param string $type The type of the entity (site, user, object).
	 * @param string $subtype The subtype of the entity.
	 * @param int $owner_guid The GUID of the object's owner.
	 * @param int $access_id The access control group to create the entity with.
	 * @param int $site_guid The site to add this entity to. Leave as 0 (default) for the current site.
	 * @return mixed The new entity's GUID, or false on failure
	 */
	function create_entity($type, $subtype, $owner_guid, $access_id, $site_guid = 0, $container_guid = 0)
	{
		global $CONFIG;
			
        $time = time();
		
        if ($site_guid == 0)
			$site_guid = $CONFIG->site_guid;
		
        if ($container_guid == 0) $container_guid = $owner_guid;
		
		if ($type=="") 
            throw new InvalidParameterException(elgg_echo('InvalidParameterException:EntityTypeNotSet'));

		return insert_data_2("INSERT into entities (type, subtype, owner_guid, site_guid, container_guid, access_id, time_created, time_updated) values (?,?,?,?,?,?,?,?)",
            array($type, $subtype, (int)$owner_guid, (int)$site_guid, (int)$container_guid, (int)$access_id, $time, $time)
        ); 
	}
	
	/**
	 * Retrieve the entity details for a specific GUID, returning it as a stdClass db row.
	 * 
	 * You will only get an object if a) it exists, b) you have access to it.
	 *
	 * @param int $guid The GUID of the object to extract
	 */
	function get_entity_as_row($guid)
	{
		global $CONFIG;
		
		if (!$guid) return false;
					
        $access = get_access_sql_suffix();
		
        return get_data_row_2("SELECT * from entities where guid=? and $access", array($guid));
	}
	
	/**
	 * Create an Elgg* object from a given entity row. 
	 */
	function entity_row_to_elggstar($row)
	{
		if (!($row instanceof stdClass))
			return $row;
			
		if ((!isset($row->guid)) || (!isset($row->subtype)))
			return $row;
			
		$new_entity = false;

		$classname = get_subtype_class_from_id($row->subtype);
		if ($classname!="")
		{
			if (class_exists($classname))
			{
				$new_entity = new $classname($row);
				
				if (!($new_entity instanceof ElggEntity))
					throw new ClassException(sprintf(elgg_echo('ClassException:ClassnameNotClass'), $classname, 'ElggEntity'));
			}
			else
				error_log(sprintf(elgg_echo('ClassNotFoundException:MissingClass'), $classname));
		}
		else
		{
			switch ($row->type)
			{
				case 'object' : 
					$new_entity = new ElggObject($row); break;
				case 'user' : 
					$new_entity = new ElggUser($row); break;
				default: 
                    throw new InstallationException(sprintf(elgg_echo('InstallationException:TypeNotSupported'), $row->type));
			}
			
		}
		
		return $new_entity;
	}
	
	/**
	 * Return the entity for a given guid as the correct object.
	 * @param int $guid The GUID of the entity
	 * @return a child of ElggEntity appropriate for the type.
	 */
	function get_entity($guid)
	{
        $cached_entity = retrieve_cached_entity($guid);
        if ($cached_entity)
            return $cached_entity;
   
		return entity_row_to_elggstar(get_entity_as_row($guid));
	}
	
    function get_entity_conditions(&$where, &$args, $params, $tableName='')
    {
        if ($tableName)
            $tableName .= ".";    
    
        $subtype = $params['subtype'];
        $type = $params['type'];
    
        if (is_array($subtype)) 
        {
            $tempwhere = "";
            
            foreach($subtype as $typekey => $subtypearray) 
            {
                foreach($subtypearray as $subtypeval) 
                {
                    if (!empty($subtypeval)) 
                    {
                        if (!$subtypeval = (int) get_subtype_id($typekey, $subtypeval))
                            return false;
                    } 
                    else 
                    {
                        // @todo: Setting subtype to 0 when $subtype = '' returns entities with
                        // no subtype.  This is different to the non-array behavior
                        // but may be required in some cases.
                        $subtypeval = 0;
                    }

                    if (!empty($tempwhere)) 
                        $tempwhere .= " or ";

                    $tempwhere .= "({$tableName}type = ? and {$tableName}subtype = ?)";
                    $args[] = $typekey;
                    $args[] = $subtypeval;
                }
            }
            if (!empty($tempwhere)) 
                $where[] = "({$tempwhere})";

        } 
        else 
        {           
            if ($type != "")
            {
                $where[] = "{$tableName}type=?";
                $args[] = $type;
            }    

            $subtypeId = get_subtype_id($type, $subtype);
            if ($subtypeId)
            {
                $where[] = "{$tableName}subtype=?";
                $args[] = $subtypeId;
            }    
        }

        $owner_guid = $params['owner_guid'];
        if ($owner_guid) 
        {
            $where[] = "{$tableName}owner_guid = ?";
            $args[] = (int)$owner_guid;
        }

        $container_guid = $params['container_guid'];
        if ($container_guid) 
        {
            $where[] = "{$tableName}container_guid = ?";
            $args[] = (int)$container_guid;
        }

        $timelower = $params['time_lower'];
        if ($timelower)
        {
            $where[] = "{$tableName}time_created >= ?";
            $args[] = (int)$timelower;
        }    
        
        $timeupper = $params['time_upper'];
        if ($timeupper)
        {
            $where[] = "{$tableName}time_created <= ?";
            $args[] = (int)$timeupper;
        }    
    }    
    
	/**
	 * Return entities matching a given query, or the number thereof
	 * 
	 * @param string $type The type of entity (eg "user", "object" etc)
	 * @param string|array $subtype The arbitrary subtype of the entity or array(type1 => array('subtype1', ...'subtypeN'), ...)
	 * @param int $owner_guid The GUID of the owning user
	 * @param string $order_by The field to order by; by default, time_created desc
	 * @param int $limit The number of entities to return; 10 by default
	 * @param int $offset The indexing offset, 0 by default
	 * @param boolean $count Set to true to get a count rather than the entities themselves (limits and offsets don't apply in this context). Defaults to false.
	 * @param int $site_guid The site to get entities for. Leave as 0 (default) for the current site; -1 for all sites.
	 * @param int|array $container_guid The container or containers to get entities from (default: all containers).
	 * @param int $timelower The earliest time the entity can have been created. Default: all
	 * @param int $timeupper The latest time the entity can have been created. Default: all
	 * @return array A list of entities. 
	 */
	function get_entities($type = "", $subtype = "", $owner_guid = 0, $order_by = "", $limit = 10, $offset = 0, $count = false, $site_guid = 0, $container_guid = null, $timelower = 0, $timeupper = 0)
	{
		global $CONFIG;
		
		if ($subtype === false || $subtype === null || $subtype === 0)
			return false;
		            
		$where = array();
        $args = array();
		
        get_entity_conditions($where, $args, array(
            'type' => $type, 
            'subtype' => $subtype, 
            'owner_guid' => $owner_guid, 
            'container_guid' => $container_guid, 
            'time_lower' => $time_lower, 
            'time_upper' => $time_upper));
        			
		if (!$count) 
        {
			$query = "SELECT * from entities where ";
		} 
        else 
        {
			$query = "SELECT count(guid) as total from entities where ";
		}
		
        foreach ($where as $w)
			$query .= " $w and ";
            
		$query .= get_access_sql_suffix(); 
		
        if (!$count) 
        {
            if ($order_by == "") 
            {
                $order_by = "time_created desc";        
            }    
            $order_by = sanitize_order_by($order_by);       
			$query .= " order by $order_by";

			if ($limit) 
            {            
                $args[] = (int)$offset;
                $args[] = (int)$limit;                
                $query .= " limit ?, ?"; 
            }    
			return array_map('entity_row_to_elggstar', get_data_2($query, $args));
		} 
        else 
        {
			$total = get_data_row_2($query, $args);
			return $total->total;
		}
	}
	
	/**
	 * Returns a viewable list of entities
	 *
	 * @see elgg_view_entity_list
	 * 
	 * @param string $type The type of entity (eg "user", "object" etc)
	 * @param string $subtype The arbitrary subtype of the entity
	 * @param int $owner_guid The GUID of the owning user
	 * @param int $limit The number of entities to display per page (default: 10)
	 * @param true|false $fullview Whether or not to display the full view (default: true)
	 * @param true|false $viewtypetoggle Whether or not to allow gallery view 
	 * @param true|false $pagination Display pagination? Default: true
	 * @return string A viewable list of entities
	 */
	function list_entities($type= "", $subtype = "", $owner_guid = 0, $limit = 10, $fullview = true, $viewtypetoggle = false, $pagination = true) {
		
		$offset = (int) get_input('offset');
		$count = get_entities($type, $subtype, $owner_guid, "", $limit, $offset, true);
		$entities = get_entities($type, $subtype, $owner_guid, "", $limit, $offset);

		return elgg_view_entity_list($entities, $count, $offset, $limit, $fullview, $viewtypetoggle, $pagination);
		
	}
	
	/**
	 * Returns a viewable list of entities contained in a number of groups.
	 *
	 * @param string $subtype The arbitrary subtype of the entity
	 * @param int $owner_guid The GUID of the owning user
	 * @param int $container_guid The GUID of the containing group
	 * @param int $limit The number of entities to display per page (default: 10)
	 * @param true|false $fullview Whether or not to display the full view (default: true)
	 * @return string A viewable list of entities
	 */
	function list_entities_groups($subtype = "", $owner_guid = 0, $container_guid = 0, $limit = 10, $fullview = true)
	{
		$offset = (int) get_input('offset');
		$count = get_objects_in_group($container_guid, $subtype, $owner_guid, 0, "", $limit, $offset, true);
		$entities = get_objects_in_group($container_guid, $subtype, $owner_guid, 0, "", $limit, $offset);

		return elgg_view_entity_list($entities, $count, $offset, $limit, $fullview);
	}	
	
	/**
	 * Disable an entity but not delete it.
	 *
	 * @param int $guid The guid
	 * @param string $reason Optional reason
	 */
	function disable_entity($guid, $reason = "", $recursive = true)
	{
		global $CONFIG;
		
		if ($entity = get_entity($guid)) 
        {		
			if (trigger_elgg_event('disable',$entity->type,$entity)) 
            {	
				if ($entity->canEdit()) 
                {				
					if ($reason)
						create_metadata($guid, 'disable_reason', $reason,'', 0, ACCESS_PUBLIC);

					if ($recursive)
					{
                        $sub_entities = $entity->getSubEntities();
                            
						if ($sub_entities) {
							foreach ($sub_entities as $e)
								$e->disable($reason);
						}							
					}
											
					$res = update_data_2("UPDATE entities set enabled='no' where guid=?", array($guid));
					
					return $res;
				} 
			}
		}
		return false;
	}
	
	/**
	 * Enable an entity again.
	 *
	 * @param int $guid
	 */
	function enable_entity($guid)
	{
		global $CONFIG;		
		
		if ($entity = get_entity($guid)) {
			if (trigger_elgg_event('enable',$entity->type,$entity)) {
				if ($entity->canEdit()) {
					
					access_show_hidden_entities($access_status);
				
					$result = update_data_2("UPDATE entities set enabled='yes' where guid=?", array($guid));
					$entity->clearMetaData('disable_reason');
					
					return $result;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Delete a given entity.
	 * 
	 * @param int $guid
	 * @param bool $recursive If true (default) then all entities which are owned or contained by $guid will also be deleted.
	 * 						   Note: this bypasses ownership of sub items.
	 */
	function delete_entity($guid, $recursive = true)
	{
		global $CONFIG;
		
		$guid = (int)$guid;
		if ($entity = get_entity($guid)) {
			if (trigger_elgg_event('delete',$entity->type,$entity)) {
					
                // Delete contained owned and otherwise releated objects (depth first)
                if ($recursive)
                {
                    $sub_entities = $entity->getSubEntities();
                    if ($sub_entities) {
                        foreach ($sub_entities as $e)
                            $e->delete();
                    }
                }

                // Now delete the entity itself
                $entity->clearMetadata();
                $entity->clearRelationships();
                remove_all_private_settings($guid);
                $res = delete_data_2("DELETE from entities where guid=?", array($guid));
                if ($res)
                {
                    $sub_table = "";

                    // Where appropriate delete the sub table
                    switch ($entity->type)
                    {
                        case 'object' : $sub_table = 'objects_entity'; break;
                        case 'user' :  $sub_table = 'users_entity'; break;
                    }

                    if ($sub_table)
                        delete_data_2("DELETE from $sub_table where guid=?", array($guid));
                }

                return $res;
			}
		}
		return false;
		
	}
	
	/**
	 * Delete multiple entities that match a given query.
	 * This function itterates through and calls delete_entity on each one, this is somewhat inefficient but lets 
	 * the 'delete' even be called for each entity.
	 * 
	 * @param string $type The type of entity (eg "user", "object" etc)
	 * @param string $subtype The arbitrary subtype of the entity
	 * @param int $owner_guid The GUID of the owning user
	 */
	function delete_entities($type = "", $subtype = "", $owner_guid = 0)
	{
		$entities = get_entities($type, $subtype, $owner_guid, "time_created desc", 0);
		
		foreach ($entities as $entity)
			delete_entity($entity->guid);
		
		return true;
	}
	
	/**
	 * Determines whether or not the specified user can edit the specified entity.
	 * 
	 * This is extendible by registering a plugin hook taking in the parameters 'entity' and 'user',
	 * which are the entity and user entities respectively
	 * 
	 * @see register_plugin_hook 
	 *
	 * @param int $entity_guid The GUID of the entity
	 * @param int $user_guid The GUID of the user
	 * @return true|false Whether the specified user can edit the specified entity.
	 */
	function can_edit_entity($entity_guid, $user_guid = 0) {
		global $CONFIG;
		
		$user_guid = (int)$user_guid;
		$user = get_entity($user_guid);
		if (!$user) $user = get_loggedin_user();

		if ($entity = get_entity($entity_guid)) {
			
			$return = false;
			
			// Test user if possible - should default to false unless a plugin hook says otherwise
			if (!is_null($user))
			{
				if ($entity->getOwner() == $user->getGUID()) $return = true;
				if ($entity->container_guid == $user->getGUID()) $return = true;
				if ($entity->type == "user" && $entity->getGUID() == $user->getGUID()) $return = true;
				if ($container_entity = get_entity($entity->container_guid)) {
					if ($container_entity->canEdit()) $return = true;
				}
			}
				
			return trigger_plugin_hook('permissions_check',$entity->type,array('entity' => $entity, 'user' => $user), $return);
		
		} else {		
			return false;
			
		}
		
	}
	
	/**
	 * Determines whether or not the specified user can edit metadata on the specified entity.
	 * 
	 * This is extendible by registering a plugin hook taking in the parameters 'entity' and 'user',
	 * which are the entity and user entities respectively
	 * 
	 * @see register_plugin_hook 
	 *
	 * @param int $entity_guid The GUID of the entity
	 * @param int $user_guid The GUID of the user
	 * @param ElggMetadata $metadata The metadata to specifically check (if any; default null)
	 * @return true|false Whether the specified user can edit the specified entity.
	 */
	function can_edit_entity_metadata($entity_guid, $user_guid = 0, $metadata = null) {
		
		if ($entity = get_entity($entity_guid)) {
			
			$return = null;
		
			if ($metadata->owner_guid == 0) $return = true;
			if (is_null($return))
				$return = can_edit_entity($entity_guid, $user_guid);
			
			$user = get_entity($user_guid);
			$return = trigger_plugin_hook('permissions_check:metadata',$entity->type,array('entity' => $entity, 'user' => $user, 'metadata' => $metadata),$return);
			return $return;

		} else {
			return false;
		}
		
	}
	
		
	/**
	 * Get the icon for an entity
	 *
	 * @param ElggEntity $entity The entity (passed an entity rather than a guid to handle non-created entities)
	 * @param string $size
	 */
	function get_entity_icon_url(ElggEntity $entity, $size = 'medium')
	{
		global $CONFIG;
		
		switch (strtolower($size))
		{
			case 'master': $size = 'master'; break;
			 
			case 'large' : $size = 'large'; break;
			
			case 'topbar' : $size = 'topbar'; break;
			
			case 'tiny' : $size = 'tiny'; break;
			
			case 'small' : $size = 'small'; break;
			
			case 'medium' :
			default: $size = 'medium';
		}
		
		$url = false;
		
		$viewtype = elgg_get_viewtype(); 
		
		// Step one, see if anyone knows how to render this in the current view
		$url = trigger_plugin_hook('entity:icon:url', $entity->getType(), array('entity' => $entity, 'viewtype' => $viewtype, 'size' => $size), $url);
		
		// Fail, so use default
		if (!$url) {

			$type = $entity->getType();
			$subtype = $entity->getSubtypeName();
			
			if (!empty($subtype)) {
				$overrideurl = elgg_view("icon/{$type}/{$subtype}/{$size}",array('entity' => $entity));
				if (!empty($overrideurl)) return $overrideurl;
			}

			$overrideurl = elgg_view("icon/{$type}/default/{$size}",array('entity' => $entity));
			if (!empty($overrideurl)) return $overrideurl;
			
			$url = $CONFIG->url . "_graphics/icons/default/$size.png";
		}
	
		return $url;
	}
		
	/**
	 * Sets the URL handler for a particular entity type and subtype
	 *
	 * @param string $function_name The function to register
	 * @param string $entity_type The entity type
	 * @param string $entity_subtype The entity subtype
	 * @return true|false Depending on success
	 */
	function register_entity_url_handler($function_name, $entity_type = "all", $entity_subtype = "all") {
		global $CONFIG;
		
		if (!is_callable($function_name)) return false;
		
		if (!isset($CONFIG->entity_url_handler)) {
			$CONFIG->entity_url_handler = array();
		}
		if (!isset($CONFIG->entity_url_handler[$entity_type])) {
			$CONFIG->entity_url_handler[$entity_type] = array();
		}
		$CONFIG->entity_url_handler[$entity_type][$entity_subtype] = $function_name;
		
		return true;
		
	}
	
	/**
	 * Default Icon URL handler for entities.
	 * This will attempt to find a default entity for the current view and return a url. This is registered at
	 * a low priority so that other handlers will pick it up first.
	 *
	 * @param unknown_type $hook
	 * @param unknown_type $entity_type
	 * @param unknown_type $returnvalue
	 * @param unknown_type $params
	 */
	function default_entity_icon_hook($hook, $entity_type, $returnvalue, $params)
	{
		global $CONFIG;
		
		if ((!$returnvalue) && ($hook == 'entity:icon:url'))
		{
			$entity = $params['entity'];
			$type = $entity->type;
			$subtype = $entity->getSubtypeName();
			$viewtype = $params['viewtype'];
			$size = $params['size'];
			
			$url = "views/$viewtype/graphics/icons/$type/$subtype/$size.png";
			
			if (!@file_exists($CONFIG->path . $url))
				$url = "views/$viewtype/graphics/icons/$type/default/$size.png";
			
			if(!@file_exists($CONFIG->path . $url))
				$url = "views/$viewtype/graphics/icons/default/$size.png";
		
			if (@file_exists($CONFIG->path . $url))
				return $CONFIG->url . $url;
		}
	}
	
	/**
	 * Registers and entity type and subtype to return in search and other places.
	 * A description in the elgg_echo languages file of the form item:type:subtype
	 * is also expected.
	 *
	 * @param string $type The type of entity (object, site, user, group)
	 * @param string $subtype The subtype to register (may be blank)
	 * @return true|false Depending on success
	 */
	function register_entity_type($type, $subtype) {
		
		global $CONFIG;
		
		$type = strtolower($type);
		if (!in_array($type,array('object','site','group','user'))) return false;
		
		if (!isset($CONFIG->registered_entities)) $CONFIG->registered_entities = array();
		$CONFIG->registered_entities[$type][] = $subtype;
		
		return true;
		
	}
	
	/**
	 * Returns registered entity types and subtypes
	 * 
	 * @see register_entity_type
	 *
	 * @param string $type The type of entity (object, site, user, group) or blank for all
	 * @return array|false Depending on whether entities have been registered
	 */
	function get_registered_entity_types($type = '') {
		
		global $CONFIG;
		
		if (!isset($CONFIG->registered_entities)) return false;
		if (!empty($type)) $type = strtolower($type);
		if (!empty($type) && empty($CONFIG->registered_entities[$type])) return false;
		
		if (empty($type))
			return $CONFIG->registered_entities;
			
		return $CONFIG->registered_entities[$type];
		
	}
	
	/**
	 * Determines whether or not the specified entity type and subtype have been registered in the system
	 *
	 * @param string $type The type of entity (object, site, user, group)
	 * @param string $subtype The subtype (may be blank)
	 * @return true|false Depending on whether or not the type has been registered
	 */
	function is_registered_entity_type($type, $subtype) {
		
		global $CONFIG;
		
		if (!isset($CONFIG->registered_entities)) return false;
		$type = strtolower($type);
		if (empty($CONFIG->registered_entities[$type])) return false;
		if (in_array($subtype, $CONFIG->registered_entities[$type])) return true;
		
	}
	
	/**
	 * Page handler for generic entities view system
	 *
	 * @param array $page Page elements from pain page handler
	 */
	function entities_page_handler($page) {
		if (isset($page[0])) {
			global $CONFIG;
			set_input('guid',$page[0]);
			@include($CONFIG->path . "entities/index.php");
		}
	}
	
	/**
	 * Returns a viewable list of entities based on the registered types
	 *
	 * @see elgg_view_entity_list
	 * 
	 * @param string $type The type of entity (eg "user", "object" etc)
	 * @param string $subtype The arbitrary subtype of the entity
	 * @param int $owner_guid The GUID of the owning user
	 * @param int $limit The number of entities to display per page (default: 10)
	 * @param true|false $fullview Whether or not to display the full view (default: true)
	 * @param true|false $viewtypetoggle Whether or not to allow gallery view 
	 * @return string A viewable list of entities
	 */
	function list_registered_entities($owner_guid = 0, $limit = 10, $fullview = true, $viewtypetoggle = false, $allowedtypes = true) {
		
		$typearray = array();
		
		if ($object_types = get_registered_entity_types()) {
			foreach($object_types as $object_type => $subtype_array) {
				if (is_array($subtype_array) && sizeof($subtype_array) && (in_array($object_type,$allowedtypes) || $allowedtypes === true))
					foreach($subtype_array as $object_subtype) {
						$typearray[$object_type][] = $object_subtype;
					}
			}
		}
		
		$offset = (int) get_input('offset');
		$count = get_entities('', $typearray, $owner_guid, "", $limit, $offset, true); 
		$entities = get_entities('', $typearray,$owner_guid, "", $limit, $offset); 

		return elgg_view_entity_list($entities, $count, $offset, $limit, $fullview, $viewtypetoggle);
		
	}
	
	/**
	 * Gets a private setting for an entity.
	 *
	 * @param int $entity_guid The entity GUID
	 * @param string $name The name of the setting
	 * @return mixed The setting value, or false on failure
	 */
	function get_private_setting($entity_guid, $name) {
		
		global $CONFIG;
 		
		if ($setting = get_data_row_2("SELECT value from private_settings where name = ? and entity_guid = ?",
            array($name, (int)$entity_guid)
        )) {
			return $setting->value;
		}
		return false;
		
	}
	
	/**
	 * Return an array of all private settings for a given
	 *
	 * @param int $entity_guid The entity GUID
	 */
	function get_all_private_settings($entity_guid) {
		global $CONFIG;
		
		$entity_guid = (int) $entity_guid;
		
        $result = get_data_2("SELECT * from private_settings where entity_guid = ?", array($entity_guid));
		if ($result)
		{
			$return = array();
			foreach ($result as $r)
				$return[$r->name] = $r->value;
			
			return $return;
		}
		
		return false;
	}
	
	/**
	 * Sets a private setting for an entity.
	 *
	 * @param int $entity_guid The entity GUID
	 * @param string $name The name of the setting
	 * @param string $value The value of the setting
	 * @return mixed The setting ID, or false on failure
	 */
	function set_private_setting($entity_guid, $name, $value) {
		
		global $CONFIG;
		
		$result = insert_data_2("INSERT into private_settings (entity_guid, name, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = ?",
            array((int)$entity_guid, $name, $value, $value)
        );
		if ($result === 0) return true;
		return $result;
		
	}
	
	/**
	 * Deletes a private setting for an entity.
	 *
	 * @param int $entity_guid The Entity GUID
	 * @param string $name The name of the setting
	 * @return true|false depending on success
	 * 
	 */
	function remove_private_setting($entity_guid, $name) 
    {	
		global $CONFIG;
		return delete_data_2("DELETE from private_settings where name = ? and entity_guid = ?",
            array($name, (int)$entity_guid));		
	}
	
	/**
	 * Deletes all private settings for an entity.
	 *
	 * @param int $entity_guid The Entity GUID
	 * @return true|false depending on success
	 * 
	 */
	function remove_all_private_settings($entity_guid) 
    {		
		global $CONFIG;
        return delete_data_2("DELETE from private_settings where entity_guid = ?", array((int)$entity_guid));
	}
		
	/**
	 * Garbage collect stub and fragments from any broken delete/create calls
	 *
	 * @param unknown_type $hook
	 * @param unknown_type $user
	 * @param unknown_type $returnvalue
	 * @param unknown_type $tag
	 */
	function entities_gc($hook, $user, $returnvalue, $tag) {
		global $CONFIG;
		
		$tables = array ('sites_entity', 'objects_entity', 'groups_entity', 'users_entity');
		
		foreach ($tables as $table) {
			delete_data_2("DELETE from {$table} where guid NOT IN (SELECT guid from entities)");
		}
	}
	
	/**
	 * Entities init function; establishes the page handler
	 *
	 */
	function entities_init() 
	{
		register_page_handler('view','entities_page_handler');				
		register_plugin_hook('gc','system','entities_gc');
	}
	
	/** Hook for rendering a default icon for entities */
	register_plugin_hook('entity:icon:url', 'all', 'default_entity_icon_hook', 1000);
	
	/** Register init system event **/
	register_elgg_event_handler('init','system','entities_init');
	
?>

<?php

    error_reporting(E_ERROR | E_PARSE);

	/**
	 * Elgg settings
	 * 
	 * Elgg manages most of its configuration from the admin panel. However, we need you to
	 * include your database settings below.
	 * 
	 * @todo Turn this into something we handle more automatically. 
	 */

		global $CONFIG;
		if (!isset($CONFIG))
			$CONFIG = new stdClass;

	/*
	 * Standard configuration
	 * 
	 * You will use the same database connection for reads and writes.
	 * This is the easiest configuration, and will suit 99.99% of setups. However, if you're
	 * running a really popular site, you'll probably want to spread out your database connections
	 * and implement database replication.  That's beyond the scope of this configuration file
	 * to explain, but if you know you need it, skip past this section. 
	 */
		
		// Database username
			$CONFIG->dbuser = 'newslink';
			
		// Database password
			$CONFIG->dbpass = 'scarlett';

		// Database name
			$CONFIG->dbname = 'elgg';
			
		// Database server
		// (For most configurations, you can leave this as 'localhost')
			$CONFIG->dbhost = 'localhost';
			
		// Database table prefix
		// If you're sharing a database with other applications, you will want to use this
		// to differentiate Elgg's tables.
			$CONFIG->dbprefix = '';

	/*
	 * Multiple database connections
	 * 
	 * Here you can set up multiple connections for reads and writes. To do this, uncomment out
	 * the lines below. 
	 */
			
	/*

		// Yes! We want to split reads and writes
			$CONFIG->db->split = true;
	 
		// READS
		// Database username
			$CONFIG->db['read']->dbuser = "";
			
		// Database password
			$CONFIG->db['read']->dbpass = "";

		// Database name
			$CONFIG->db['read']->dbname = "";
			
		// Database server
		// (For most configurations, you can leave this as 'localhost')
			$CONFIG->db['read']->dbhost = "localhost";

		// WRITES
		// Database username
			$CONFIG->db['write']->dbuser = "";
			
		// Database password
			$CONFIG->db['write']->dbpass = "";

		// Database name
			$CONFIG->db['write']->dbname = "";
			
		// Database server
		// (For most configurations, you can leave this as 'localhost')
			$CONFIG->db['write']->dbhost = "localhost";


	 */
			
	/*
	 * For extra connections for both reads and writes, you can turn both
	 * $CONFIG->db['read'] and $CONFIG->db['write'] into an array, eg:
	 * 
	 * 	$CONFIG->db['read'][0]->dbhost = "localhost";
	 * 
	 * Note that the array keys must be numeric and consecutive, i.e., they start
	 * at 0, the next one must be at 1, etc.
	 */
	 
			
	/**
	 * Memcache setup (optional)
	 * This is where you may optionally set up memcache.
	 * 
	 * Requirements: 
	 * 	1) One or more memcache servers (http://www.danga.com/memcached/)
	 *  2) PHP memcache wrapper (http://uk.php.net/manual/en/memcache.setup.php)
	 * 
	 * Note: Multiple server support is only available on server 1.2.1 or higher with PECL library > 2.0.0
	 */
	//$CONFIG->memcache = true;
	//
	//$CONFIG->memcache_servers = array (
	//	array('server1', 11211),
	//	array('server2', 11211)
	//);		
	
	/**
	 * Some work-around flags.
	 */
	
	// Try uncommenting the below if your notification emails are not being sent
	// $CONFIG->broken_mta = true; 
			
    $CONFIG->email_pass = "f03;aoeA";    		
    $CONFIG->google_api_key = "ABQIAAAAHy69XWEjciJIVElz0OYMsRR3-IOatrPZ1tLat998tYHgwqPnkhTKyWcq8ytRPMx3RyxFjK0O7WSCHA";
        
    $CONFIG->translations['sw'] = array('sw' => 'Kiswahili');

    $CONFIG->path = dirname(dirname(__FILE__)) . "/";   
    $CONFIG->viewpath = $CONFIG->path . "views/";   
    $CONFIG->pluginspath = $CONFIG->path . "mod/";    
    $CONFIG->dataroot = dirname($CONFIG->path). "/elgg-data/";
    $CONFIG->simplecache_enabled = 0;
    $CONFIG->viewpath_cache_enabled = 0;
    $CONFIG->wwwroot = "http://localhost/elgg/";
    $CONFIG->url = $CONFIG->wwwroot;
    $CONFIG->view = "default";
    $CONFIG->language = "en";
    $CONFIG->default_access = "1";
    $CONFIG->allow_user_default_access = "0";
    $CONFIG->debug = "1";
    $CONFIG->site_guid = $CONFIG->site_id = 1;
    $CONFIG->sitename = "Envaya";
    $CONFIG->sitedescription = "";
    $CONFIG->siteemail = "youngj@envaya.org";
    $CONFIG->enabled_plugins = array("envaya","diagnostics","logbrowser","profile","googlegeocoder");
    
    $CONFIG->subtypes = array(
        1 => array("object", "file", "ElggFile"),
        2 => array("object", "plugin", "ElggPlugin"),
        3 => array("object", "widget", "ElggWidget"),
        4 => array('user', 'organization', "Organization"),
        5 => array('object', 'translation', 'Translation'),
        7 => array('object', 'blog', 'NewsUpdate'),        
    );
    
    include_once(dirname(__FILE__) . "/localsettings.php");
?>
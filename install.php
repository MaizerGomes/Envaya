<?php

    /**
     * Elgg install script
     *
     * @package Elgg
     * @subpackage Core

     * @author Curverider Ltd

     * @link http://elgg.org/
     */

    require_once(__DIR__ . "/engine/start.php");

    elgg_set_viewtype('failsafe');
    if (is_installed())
    {
        forward("index.php");
    }
    else
    {
        run_sql_script(__DIR__ . "/engine/schema/mysql.sql");
        init_site_secret();
        system_message(__("installation:success"));
        datalist_set('installed', 1);
        system_message(__("installation:configuration:success"));
        forward("pg/register");
    }

?>
<?php

    require_once("scripts/cmdline.php");
    require_once("start.php");
    require_once("vendors/s3.php");

    include "scripts/delete_old_backups.php";
    
    $start = microtime(true);
    umask(0);
    
    $now = date("YmdHi");
    $dbname = Config::get('db:name');    
    
    $s3_path = "$dbname$now.sql.gz.nc";
    
    $fs_path = Config::get('dataroot') . "/dump/$dbname.sql.gz.nc";
    
    $output_dir = dirname($fs_path);
    
    if (!is_dir($output_dir))
    {
        mkdir($output_dir, 0777, true);
    }
    
    $dump = "mysqldump ".escapeshellarg($dbname)
        ." -u ".escapeshellarg(Config::get('task:db_backup_user'))
        ." --password=".escapeshellarg(Config::get('task:db_backup_password'))
        ." --default-character-set=latin1 -N"; // workaround for double utf-8 encoding bug

    $crypt = "mcrypt -q --key ".escapeshellarg(Config::get('db:password'));

    echo system("$dump | gzip | $crypt > $fs_path && chmod 666 $fs_path");    
    
    $s3 = new S3(Config::get('storage:s3_key'), Config::get('storage:s3_private'));    
    if (!$s3->uploadFile(Config::get('task:s3_backup_bucket'), $s3_path, $fs_path))
    {
        throw new Exception("S3 upload returned false");
    }
    
    $info = $s3->getObjectInfo(Config::get('task:s3_backup_bucket'), $s3_path);
    
    $size = (int)$info['Content-Length'];
    
    $end = microtime(true);
    
    $elapsed = $end - $start;
    
    State::set('backup_time', timestamp());
    State::set('backup_info', "$s3_path / $size bytes / $elapsed sec");

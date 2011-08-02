<?php

/* 
 * Overridden values of config settings for selenium tests.
 * Selenium tests use a separate domain and data store from the normal development environment.
 */

$dataroot = __DIR__.'/data';

return array(        
    'captcha_enabled' => false,
    'ssl_enabled' => false,
    'mock_mail_file' => "$dataroot/mail.out",
    'domain' => 'localhost:3001',
    'queue_host' => 'localhost',
    'queue_port' => 22134,
    'sphinx_port' => 9313,    
    'dataroot' => $dataroot,        
    'sphinx_conf_dir' => $dataroot,
    'sphinx_log_dir' => $dataroot,
    'sphinx_pid_dir' => $dataroot,
    'dbname' => 'envaya_test',
);
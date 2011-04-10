<?php

// Prompt user at command line to create admin user account

require_once("scripts/cmdline.php");
require_once("engine/start.php");

$username = prompt_default("Admin username", "testadmin");
$password = prompt_default("Admin password", "testtest");
$name = prompt_default("Admin name", "Test Admin");
$email = prompt_default("Admin email", '');

$new_user = register_user($username, $password, $name, $email);
$new_user->admin = true;    
$new_user->save();
echo "Admin created\n";

Config::set('debug', false);
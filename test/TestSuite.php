<?php

/*
 Command-line script for running Envaya's Selenium tests.
 
 usage: php TestSuite.php
    --test=TestClass1 --test=TestClass2 (from testcases directory, omit to run all test cases)
    --browser=*browser (selenium browser expression, omit to use *firefox)
    
    Assumes that http://localhost is running Envaya code, and no other Envaya services are running on localhost.    
    
    In Firefox, tries to run tests with Flash disabled.
    
    Firefox does not make it easy to programmatically disable plugins like Flash.
    On Windows, the path to Flash's DLL in test/profiles/noflash/pluginreg.dat must be exactly the same (case sensitive) as:
        registry key: HKEY_LOCAL_MACHINE\SOFTWARE\MozillaPlugins\@adobe.com/FlashPlayer ; 
        value name: Path    
*/

require_once dirname(__DIR__).'/scripts/cmdline.php';
require_once dirname(__DIR__).'/engine/config.php';
require_once 'PHPUnit/Autoload.php';
require_once __DIR__.'/SeleniumTest.php';
require_once __DIR__.'/WebDriverTest.php';
require_once __DIR__.'/webdriver/phpwebdriver/WebDriver.php';

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__DIR__).'/vendors/zend');
require_once 'Zend/Loader.php';

Config::load();

function kill_windows_process_tree($pid, $wmi = null)
{
    //echo "kill $pid\n";

    if ($wmi == null) 
    {
        $wmi = new COM("winmgmts:{impersonationLevel=impersonate}!\\\\.\\root\\cimv2"); 
    }

    // adapted from http://www.php.net/manual/en/function.posix-kill.php#102094
    
    $procs = $wmi->ExecQuery("SELECT * FROM Win32_Process WHERE ProcessId='$pid'");     
    $children = $wmi->ExecQuery("SELECT * FROM Win32_Process WHERE ParentProcessId='$pid'"); 
    
    foreach ($procs as $proc)
    {
        $proc->Terminate();
        break;
    }
    
    foreach ($children as $child) 
    {
        kill_windows_process_tree($child->ProcessId, $wmi);
    }
}

function get_all_test_cases()
{
    // each php file in test/testcases/ is assumed to be a test class with the same name as the file.
    $paths = get_test_case_paths();
    
    return array_map(function($path) { 
        $pathinfo = pathinfo($path);
        return $pathinfo['filename'];
    }, $paths);
}

function get_test_case_paths($test_case = "*")
{    
    // look in test/testcases/ in all the enabled modules
    chdir(dirname(__DIR__));
    $modules = explode("\n", `php scripts/module_list.php`);
    $module_glob = "{".implode(",", $modules)."}";

    return glob("{test/testcases/$test_case.php,mod/$module_glob/test/testcases/$test_case.php}", GLOB_BRACE);
}

function get_test_case_path($test_case)
{        
    $paths = get_test_case_paths($test_case);
    if (sizeof($paths) > 0)
    {
        return $paths[0];
    }
    throw new Exception("$test_case not found");
}

function check_selenium()
{
    $handle = @fsockopen("localhost", 4444, $errno, $errstr, 1);
    if (!$handle) { 
         echo "waiting for selenium server to respond...\n";
         sleep(1);  
         throw new Exception("selenium server not responding"); 
    }
    fclose($handle);
}

function stop_selenium()
{
    $sel = new Testing_Selenium("",""); 
    $sel->shutDownSeleniumServer();
}

function run_test_suite($suite, $opts)
{
    if (!isset($opts['selenium']))
    {
        $opts['selenium'] = true;
    }

    if (!isset($opts['runserver']))
    {
        $opts['runserver'] = true;
    }    
    
    if (!isset($opts['reset']))
    {
        $opts['reset'] = true;
    }
    
    global $BROWSER;
    $BROWSER = @$opts['browser'] ?: 'firefox';

    global $TEST_CONFIG;    
    $TEST_CONFIG = include __DIR__."/config.php";       
    
    $test_dataroot = $TEST_CONFIG['dataroot'];
    
    if ($opts['reset'])
    {    
        chdir(dirname($test_dataroot));
        system("rm -rf test_data");       
    }
    chdir(__DIR__);
    
    umask(0);
    @mkdir($test_dataroot, 0777, true);       
    
    if ($opts['selenium'])
    {
        require_once dirname(__DIR__)."/scripts/install_selenium.php";
    
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("file", "$test_dataroot/selenium.out", 'w'),
           2 => array("file", "$test_dataroot/selenium.err.out", 'w')
        );
            
        $selenium_path = Config::get('dataroot') . "/" . Config::get('selenium_jar');    
        
        $selenium = proc_open("java -jar $selenium_path -singleWindow", 
            $descriptorspec, $pipes, __DIR__);  
    }
    
    $env = get_environment();    
    $env["ENVAYA_CONFIG"] = json_encode($TEST_CONFIG);            
    $root = dirname(__DIR__);        
        
    if ($opts['reset'])
    {
        run_task_sync("php test/reset_db.php | mysql -u root", $root);
        run_task_sync('php scripts/install_tables.php', $root, $env);    
        run_task_sync('php scripts/install_kestrel.php', $root, $env);
        run_task_sync('php scripts/install_sphinx.php', $root, $env);
        run_task_sync('php scripts/install_test_data.php', $root, $env);   
    }
     
    if ($opts['runserver'])
    {     
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("file", "$test_dataroot/runserver.out", 'w'),
           2 => STDERR
        );                
      
        $runserver = proc_open('php runserver.php', $descriptorspec, $runserver_pipes, $root, $env);    
        $runserver_status = proc_get_status($runserver);                
        posix_setpgid($runserver_status['pid'], $runserver_status['pid']);   
    }
    
    if ($opts['selenium'])
    {
        retry('check_selenium');
    }
    sleep(2);

    PHPUnit_TextUI_TestRunner::run($suite);
    
    if ($opts['runserver'])
    {    
        if (function_exists('posix_kill'))
        {    
            posix_kill(-$runserver_status['pid'], SIGTERM);
        }
        else
        {
            kill_windows_process_tree($runserver_status['pid']);
        }
    }
    
    if ($opts['selenium'])
    {
        stop_selenium();
    }
}

function main()
{    
    $opts = getopt('',array("browser:","test:",));
     
    if (@$opts['test'])
    {
        $test_cases = is_array($opts['test']) ? $opts['test'] : array($opts['test']);
    }
    else
    {    
        $test_cases = get_all_test_cases();    
    }
    
    $suite = new PHPUnit_Framework_TestSuite('Envaya');

    foreach ($test_cases as $test_case)
    {
        require_once get_test_case_path($test_case);
        $suite->addTestSuite($test_case);
    }   
    
    run_test_suite($suite, $opts);
}

if (realpath($argv[0]) == __FILE__)
{
    main(); 
}

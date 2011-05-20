<?php

/*
 * A long-running command line task that spawns short-lived worker processes 
 * to execute queued functions (e.g. sending emails). 
 *
 * On a server it is run as a daemon by /etc/init.d/queueRunner, but can also
 * be run directly (php scripts/queueRunner.php) in a development environment.
 *
 * Workers are short-lived so that memory leaks in tasks do not matter,
 * as the worker's memory will be reclaimed by the operating system
 * when the worker exits. This master process should use very little memory.
 */

require_once "scripts/cmdline.php";

// times in seconds
define('START_BAD_WORKER_INTERVAL', 120);
define('GOOD_WORKER_MIN_LIFE', 8);
define('WORKER_CHECK_INTERVAL', 1);

$worker_options = array(
    array(
        'cmd' => 'php scripts/workers/call.php'
    ),
    array(
        'cmd' => 'php scripts/workers/feeds.php'
    ),    
);

$workers = array();

class WorkerProcess
{
    public $cmd;
    public $start_error = false;
    public $start_time = null;
    private $process = null;    
    public $pid;

    function __construct($options)
    {
        foreach ($options as $k => $v)
        {
            $this->$k = $v;
        }
    }
        
    function is_running()
    {
        if (!$this->process)
            return false;
            
        $status = proc_get_status($this->process);                
        return $status['running'];    
    }
        
    function start()
    {        
        $this->start_error = false;
        $this->start_time = time();
        
        $this->process = run_task($this->cmd);

        if (!is_resource($this->process))
        {
            error_log("Error starting worker {$this->cmd}\n");
            $this->process = null;
            $this->start_error = true;
            return;
        }
        
        $status = proc_get_status($this->process);                
                
        if (!$status['running'])
        {
            error_log("worker $cmd not running\n");
            $this->start_error = true;
            $this->process = null;
            return;
        }
        
        $this->pid = $status['pid'];
        // set process group id on worker so if it spawns child processes we can kill them all
        posix_setpgid($this->pid, $this->pid);   
    }
    
    function kill()
    {
        if ($this->process)
        {
            $status = proc_get_status($this->process);
            
            if ($status['running'])
            {
                // negative pid means kill all processes in worker process group
                posix_kill(-$status['pid'], SIGTERM);
                proc_close($this->process);                
            }
            $this->process = null;
        }
    }        
}

function sig_handler($signo)
{
    global $workers;
    
    foreach ($workers as $worker)
    {
        $worker->kill();
    }
    exit;
}

function run_forever()
{
    pcntl_signal(SIGTERM, "sig_handler");

    global $workers, $worker_options;
    
    // start all workers initially
    foreach ($worker_options as $worker_option)
    {        
        $worker = new WorkerProcess($worker_option);        
        $workers[] = $worker;
        $worker->start();
    }
    
    // keep restarting workers when they exit
    while (true)
    {
        $time = time();
   
        foreach ($workers as $worker)
        {        
            $time_elapsed = $time - $worker->start_time;
        
            // ignore workers that fail to start, but try again occasionally in case it's just a temporary problem
            if ($worker->start_error && $time_elapsed < START_BAD_WORKER_INTERVAL)
                continue;
        
            if (!$worker->is_running())
            {
                if ($time_elapsed < GOOD_WORKER_MIN_LIFE)
                {
                    // if a worker dies very soon after starting, count it as a start error
                    // so we ignore this worker for a while
                    $worker->start_error = true;
                }
                else
                {        
                    $worker->start();
                }
            }
        }
        pcntl_signal_dispatch();
        sleep(WORKER_CHECK_INTERVAL);
    }
}

run_forever();
<?php

require_once("scripts/cmdline.php");
require_once("engine/start.php");

$startTime = time();
$maxWorkerTime = 60;

function sig_handler($signo)
{
     exit;
}

pcntl_signal(SIGTERM, "sig_handler");

while (time() - $startTime < $maxWorkerTime)
{
    if (!FunctionQueue::exec_queued_call($timeout = 500))
    {
        sleep(1);
        pcntl_signal_dispatch();
    }
}
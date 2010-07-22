<?php

function queue_connect()
{
    global $CONFIG;
    static $kestrel;
    
    if (!isset($kestrel))
    {
        $kestrel = new Memcache;
        if (!$kestrel->connect($CONFIG->queue_host, $CONFIG->queue_port))
        {
            throw new IOException(__("IOException:QueueConnectFailed"));
        }
    }
    return $kestrel;
}


function queue_function_call($fn, $args)
{    
    $kestrel = queue_connect();

    if (!$kestrel->set('call', serialize(array('fn' => $fn, 'args' => $args))))
    {
        throw new IOException(__("IOException:QueueAppendFailed")); 
    }
    return true;
}

function exec_queued_function_call($timeout = 0)
{
    $kestrel = queue_connect();

    if ($nextCallStr = $kestrel->get("call/t=$timeout"))
    {   
        $nextCall = unserialize($nextCallStr);
        call_user_func_array($nextCall['fn'], $nextCall['args']);
        return true;
    }
    return false;
}

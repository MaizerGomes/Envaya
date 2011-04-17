<?php

class IOException extends Exception {}
class SecurityException extends Exception {}
class DatabaseException extends Exception {}
class CallException extends Exception {}
class DataFormatException extends Exception {}
class NotImplementedException extends CallException {}
class InvalidParameterException extends CallException {}
class NotFoundException extends Exception {}
class ValidationException extends Exception {}

/**
 * PHP Error handler function.
 * This function acts as a wrapper to catch and report PHP error messages.
 *
 * @see http://www.php.net/set-error-handler
 * @param int $errno The level of the error raised
 * @param string $errmsg The error message
 * @param string $filename The filename the error was raised in
 * @param int $linenum The line number the error was raised at
 * @param array $vars An array that points to the active symbol table at the point that the error occurred
 */
function php_error_handler($errno, $errmsg, $filename, $linenum, $vars)
{            
    if (error_reporting() == 0) // @ sign
        return true; 
           
    $error = date("Y-m-d H:i:s (T)") . ": \"" . $errmsg . "\" in file " . $filename . " (line " . $linenum . ")";                      

    switch ($errno) {
        case E_USER_ERROR:
                error_log("ERROR: " . $error);
                SessionMessages::add_error("ERROR: " . $error);

                // Since this is a fatal error, we want to stop any further execution but do so gracefully.
                throw new Exception($error);
            break;

        case E_WARNING :
        case E_USER_WARNING :
                error_log("WARNING: " . $error);                        
            break;

        default:
            if (Config::get('debug'))
            {
                error_log("DEBUG: " . $error);
            }
    }

    return true;
}

/**
 * Custom exception handler.
 * This function catches any thrown exceptions and handles them appropriately.
 *
 * @see http://www.php.net/set-exception-handler
 * @param Exception $exception The exception being handled
 */

function php_exception_handler($exception) {
    
    error_log("*** FATAL EXCEPTION *** : " . $exception);
    
    if (ob_get_level() > 0)
    {    
        ob_end_clean(); // Wipe any existing output buffer
    }
    
    if (@$_SERVER['REQUEST_URI'])
    {    
        header("HTTP/1.1 500 Internal Server Error");
        
        $request = Request::current();
        
        if (@$request->headers['Content-Type'] == 'text/javascript')
        {
            echo json_encode(array(
                'error' => $exception->getMessage(), 
                'errorClass' => get_class($exception)
            ));
        }
        else
        {   
            echo view('layouts/default', array(
                'title' => __('exception_title'),
                'header' => view('page_elements/title', array('title' => __('exception_title'))),
                'content' => view("messages/exception", array('object' => $exception))
            ));
        }
    }
    else // CLI
    {
        echo $exception;
    }

    if (Config::get('error_emails_enabled'))
    {
        $lastErrorEmailTimeFile = Config::get('dataroot')."last_error_time";
        $lastErrorEmailTime = (int)file_get_contents($lastErrorEmailTimeFile);
        $curTime = time();

        if ($curTime - $lastErrorEmailTime > 60)
        {
            file_put_contents($lastErrorEmailTimeFile, "$curTime", LOCK_EX);

            $class = get_class($exception);
            $ex = print_r($exception, true);
            $server = print_r($_SERVER, true);

            OutgoingMail::create(
                "$class: {$_SERVER['REQUEST_URI']}", 
"Exception:
==========
$ex


_SERVER:
=======
$server
        ")->send_to_admin();
        }
    }
}

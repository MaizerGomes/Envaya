<?php

class Controller_SMSGateway extends Controller
{
    static $routes = array(
        array(
            'regex' => '/(?P<action>\w+)\b',
            'defaults' => array('action' => 'index'),
        ),        
    );

    function action_app()
    {
        if (!Request::is_post())
        {
            $this->page_draw(array(
                'title' => "EnvayaSMS Android App",
                'content' => view('page/sms_app'),
            ));
            
            return;
        }

        //error_log(var_export($_POST, true));
        //error_log(var_export($_FILES, true));
        
        /*
        foreach ($_FILES as $name => $file)
        {
            copy($file['tmp_name'], Config::get('dataroot') . "/mms/" . $file['name']);
        }*/
    
        $provider = new SMS_Provider_App();
    
        if (!$provider->is_validated_request())
        {
            $this->log("{$provider->get_log_line()} INVALID");
        
            $this->set_status(403);
            $this->set_content("Invalid request signature");
            throw new RequestAbortedException();
        }    
        
        $request = $provider->request;
    
        $action = $request->get_action();
    
        switch ($action->type)
        {
            case EnvayaSMS::ACTION_INCOMING:
                return $this->receive_sms($provider);
            case EnvayaSMS::ACTION_OUTGOING:
                   
                $messages = array();
                
                $message_ids = array();

                foreach (OutgoingSMS::query()
                    ->where('status = ?', OutgoingSMS::Queued)
                    ->where('from_number = ?', $request->phone_number)
                    ->where('time_sendable <= ?', timestamp())
                    ->filter() as $sms)
                {
                    $message = new EnvayaSMS_OutgoingMessage();
                    $message->id = $sms->id;
                    $message->message = $sms->message;
                    $message->to = $sms->to_number;
                    $messages[] = $message;
                    
                    $message_ids[] = $message->id;
                }                       
                
                $this->set_content_type('text/xml');
                $this->set_content($action->get_response_xml($messages));                   
                        
                $num_messages = sizeof($messages);
                $message_ids_str = implode(',', $message_ids);
                        
                $this->log("{$request->phone_number} out App v{$request->version} $num_messages $message_ids_str");
                        
                return;
            case EnvayaSMS::ACTION_SEND_STATUS:    

                $message = OutgoingSMS::query()->where('id = ?', $action->id)->get();
                
                if (!$message)
                {
                    $this->log("{$request->phone_number} stat App v{$request->version} {$message->id} not_found");
                
                    $this->set_status(404);
                    $this->set_content("Message does not exist");        
                    return;
                }
                                
                $message->status = $this->parse_sms_status($action->status);
                
                if ($message->status == OutgoingSMS::Sent)
                {
                    $message->time_sent = timestamp();
                }
                
                $message->error_message = $action->error;                
                $message->save();    
                $this->set_content("OK");    
                
                $this->log("{$request->phone_number} stat App v{$request->version} {$action->id} {$action->status}");            
                return;

            default:
                throw new NotFoundException();
        }
    }    
    
    private function parse_sms_status($sms_status)
    {
        switch ($sms_status)
        {
            case EnvayaSMS::STATUS_QUEUED:
                return OutgoingSMS::Queued;
            case EnvayaSMS::STATUS_SENT:
                return OutgoingSMS::Sent;
            case EnvayaSMS::STATUS_FAILED:
                return OutgoingSMS::Failed;
            default:
                return 0;
        }
    }   
    
    function log($str)
    {
        $log_file = Config::get('dataroot'). '/sms.log';
    
        $time = timestamp();
        $date = new DateTime("@$time");
        $date_str = $date->format("Y-m-d H:i:s");
    
        $f = fopen($log_file, "a");
        fwrite($f, "[$date_str] $str\n");
        fclose($f);
    }
        
    private function get_incoming_sms_route($to_number)
    {
        foreach (Config::get('sms_routes') as $route)
        {
            if ($route['self_number'] === $to_number)
            {
                return $route;
            }
        }
        return null;
    }
    
    private function is_valid_provider_class($provider_class)
    {
        foreach (Config::get('sms_routes') as $route)
        {
            if ($route['provider'] === $provider_class)
            {
                return true;
            }
        }
        return false;    
    }
    
    function action_incoming()
    {
        $provider_class = "SMS_Provider_".get_input('provider');
        
        if (!$this->is_valid_provider_class($provider_class))
        {        
            $this->log("$provider_class UNKNOWN");
        
            $this->set_status(403);
            $this->set_content("Invalid SMS provider");
            throw new RequestAbortedException();
        }        
        
        $provider = new $provider_class();        
    
        if (!$provider->is_validated_request())
        {
            $this->log("{$provider->get_log_line()} INVALID");
        
            $this->set_status(403);
            $this->set_content("Invalid request signature");
            throw new RequestAbortedException();
        }  

        $this->receive_sms($provider);          
    }
    
    private function receive_sms($provider)
    {
        $to_number = $provider->get_request_to();
        $route = $this->get_incoming_sms_route($to_number);
                
        if (!$route)
        {
            $this->log("{$provider->get_log_line()} NO_ROUTE");
        
            $this->set_status(404);
            $this->set_content("Incoming SMS did not match any routes");
            throw new RequestAbortedException();
        }        
    
        $service_class = $route['service'];
        $service = new $service_class();
                
        $request = new SMS_Request($service, $provider);
            
        $initial_controller = $request->get_initial_controller();
        
        $initial_controller->set_request($request);
        
        $controller = $initial_controller->execute($request->get_message()) ?: $initial_controller;
        
        $request->save_state();
        
        $replies = $controller->get_replies();
        
        $provider->render_response($replies, $this);
        
        $num_replies = sizeof($replies);
        $length = 0;
        foreach ($replies as $reply)
        {
            $length += strlen($reply);
        }
                
        $action = str_replace('action_','',$controller->param('action'));
        $controller_cls = str_replace('SMS_Controller_','',get_class($controller));
                
        $this->log("{$provider->get_log_line()} $controller_cls::$action $num_replies $length");
    }    
}
    

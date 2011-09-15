<?php

class Controller_SMSGateway extends Controller
{
    static $routes = array(
        array(
            'regex' => '/(?P<action>\w+)\b',
            'defaults' => array('action' => 'index'),
        ),        
    );

    function action_kalsms()
    {
        if (!Request::is_post())
        {
            $this->page_draw(array(
                'title' => "KalSMS",
                'content' => view('page/kalsms'),
            ));
            
            return;
        }
    
        $provider = new SMS_Provider_KalSMS();
    
        if (!$provider->is_validated_request())
        {
            $this->set_status(403);
            $this->set_content("Invalid request signature");
            throw new RequestAbortedException();            
        }    
        
        $kalsms = $provider->kalsms;
    
        $action = $kalsms->get_request_action();
    
        switch ($action->type)
        {
            case KalSMS::ACTION_INCOMING:
                error_log("incoming sms");
                return $this->receive_sms($provider);
            case KalSMS::ACTION_OUTGOING:
                   
                $messages = array();

                foreach (OutgoingSMS::query()
                    ->where('status = ?', OutgoingSMS::Queued)
                    ->where('from_number = ?', $kalsms->get_request_phone_number())
                    ->where('time_sendable <= ?', timestamp())
                    ->filter() as $sms)
                {
                    $message = new KalSMS_OutgoingMessage();
                    $message->id = $sms->id;
                    $message->message = $sms->message;
                    $message->to = $sms->to_number;
                    $messages[] = $message;
                }                       
                
                $this->set_content_type('text/xml');
                $this->set_content($action->get_response_xml($messages));                   
                        
                return;
            case KalSMS::ACTION_SEND_STATUS:    

                $message = OutgoingSMS::query()->where('id = ?', $action->id)->get();
                
                if (!$message)
                {
                    $this->set_status(404);
                    $this->set_content("Message does not exist");        
                    return;
                }
                                
                $message->status = $this->get_status_from_kal_status($action->status);
                
                if ($message->status == OutgoingSMS::Sent)
                {
                    $message->time_sent = timestamp();
                }
                
                $message->error_message = $action->error;                
                $message->save();    
                $this->set_content("OK");    
            
                return;

            default:
                throw new NotFoundException();
        }
    }    
    
    private function get_status_from_kal_status($kal_status)
    {
        switch ($kal_status)
        {
            case KalSMS::STATUS_QUEUED:
                return OutgoingSMS::Queued;
            case KalSMS::STATUS_SENT:
                return OutgoingSMS::Sent;
            case KalSMS::STATUS_FAILED:
                return OutgoingSMS::Failed;
            default:
                return 0;
        }
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
            $this->set_status(403);
            $this->set_content("Invalid SMS provider");
            throw new RequestAbortedException();
        }        
        
        $provider = new $provider_class();        
    
        if (!$provider->is_validated_request())
        {
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
            $this->set_status(404);
            $this->set_content("Incoming SMS did not match any routes");
            throw new RequestAbortedException();
        }        
    
        $service_class = $route['service'];
        $service = new $service_class();
        
        $from_number = $provider->get_request_from();
        $message = $provider->get_request_message();
        
        $request = new SMS_Request($service, $from_number, $to_number, $message);
    
        $initial_controller = $request->get_initial_controller();
        
        $initial_controller->set_request($request);
        
        $controller = $initial_controller->execute($request->get_message()) ?: $initial_controller;
        
        $request->save_state();
        
        $replies = $controller->get_replies();        
        
        $provider->render_response($replies, $this);
    }    
}
    

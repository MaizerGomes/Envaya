<?php

class ExternalFeed extends Entity
{
    const Idle = 0;
    const Queued = 1;
    const Updating = 2;

    static $table_name = 'external_feeds';
    static $table_attributes = array(
        'subtype_id' => '',
        'url' => '',
        'feed_url' => '',
        'title' => '',
        'update_status' => 0,
        'time_next_update' => 0,
        'time_queued' => 0,
        'time_update_started' => 0,
        'time_update_complete' => 0,
        'time_changed' => 0,
        'time_last_error' => 0,
        'last_error' => '',
        'consecutive_errors' => 0,
    );
    
    static $regexes = array(
        '#://[^/]*facebook\.com#i' => 'ExternalFeed_Facebook',
        '#://[^/]*twitter\.com#i' => 'ExternalFeed_Twitter',
    );
    
    function get_widget_subclass() { throw new NotImplementedException("ExternalFeed::get_widget_subclass"); }
    protected function _update() { throw new NotImplementedException("ExternalFeed::_update"); }

    function get_default_update_interval()
    {    
        return 5 * 60;
    }
    
    function calculate_next_update_time()
    {
        $interval = $this->get_default_update_interval();        
        
        $time = time();
        
        // gradually slow down checking feeds that do not change often
        if ($this->time_changed && $this->time_changed < $time)
        {
            $days_since_changed = floor(($time - $this->time_changed) / 86400.0);
            $interval *= min((1 + $days_since_changed * 0.5), 500);
        }
        
        // exponential backoff to slow down rechecking broken feeds
        $interval *= min(pow(2, $this->consecutive_errors), 2048); 
                
        $this->time_next_update = $time + $interval;
    }
    
    function queue_update()
    {
        $this->update_status = static::Queued;
        $this->time_queued = time();
        $this->save();
    
        return FunctionQueue::queue_call(array('ExternalFeed', 'update_by_guid'), array($this->guid));    
    }    
    
    function get_widget_by_external_id($id)
    {
        // ensure widget_name is less than size of database column        
        $widget_name = "{$this->subtype_id}:" . ((strlen($id) < 90) ? $id : md5($id));
        $container = $this->get_container_entity();
                
        return $container->get_widget_by_name(
            $widget_name, 
            $this->get_widget_subclass()
        );
    }
    
    static function update_by_guid($guid)
    {
        $feed = ExternalFeed::query()->guid($guid)->get();
        if ($feed)
        {
            $feed->update();
        }
    }

    function can_update()
    {
        if (!$this->is_enabled())
            return false;
    
        $container = $this->get_container_entity();
        if (!$container || !$container->is_enabled())
            return false;

        $root = $this->get_root_container_entity();
        if (!$root || !$root->is_enabled())
            return false;
            
        if (time() - $this->time_update_started < $this->get_default_update_interval())
        {
            return false;
        }
        
        return true;
    }
    
    function update()
    {    
        if (!$this->can_update())
        {
            echo "ignoring {$this->feed_url} for now...\n";
            $this->update_status = static::Idle;
            $this->save();
            return;
        }
        
        $this->update_status = static::Updating;
        $this->time_update_started = time();
        $this->save();
        
        echo "updating {$this->feed_url}...\n";
            
        try
        {
            $changed = $this->_update();
            if ($changed)
            {
                $this->time_changed = time();
            }
            
            $this->time_update_complete = time();
            $this->consecutive_errors = 0;
            $this->calculate_next_update_time();         
            $this->update_status = static::Idle;
            $this->save();            
        }
        catch (Exception $ex)
        {
            $msg = get_class($ex).": ".$ex->getMessage();
            error_log("error updating external feed {$this->feed_url}: $msg");
            
            $this->time_last_error = time();
            $this->last_error = $msg;
            $this->consecutive_errors += 1;
            $this->calculate_next_update_time();
            $this->update_status = static::Idle;
            $this->save();            
       
            if ($ex instanceof IOException || $ex instanceof DataFormatException) 
            {
                // suppress exception for feeds that are broken in expected ways
            }
            else
            {                   
                throw $ex;
            }
        }
    }
        
    static function validate_url($url)
    {
        Web_Request::validate_url($url);            
        if (static::is_local_url($url))
        {
            throw new ValidationException(
                strtr(__('web:invalid_url'), array('{url}' => $url))."\n".
                __('web:try_again')
            );
        }            
    }
    
    static function validate_subclass($cls)
    {
        if (!$cls || !preg_match('/^ExternalFeed_/', $cls))
        {
            throw new ValidationException("Invalid feed class: " . $cls);           
        }
        return $cls;        
    }       
    
    static function is_local_url($url)
    {
        $parsed = parse_url($url);
        $host = strtolower($parsed['host']);
        if (strpos($host, Config::get('domain')) !== false
            || OrgDomainName::get_username_for_host($host))
        {
            return true;
        }
        else
        {
            return false;
        }
    }               
    
    static function try_new_from_web_response($response)
    {
        $headers = $response->headers;
        $url = $response->url;
        
        if (static::is_local_url($url))
        {
            return null;
        }
         
        $feed_cls = 'ExternalFeed_RSS';
        foreach (static::$regexes as $regex => $cls)
        {
            if (preg_match($regex, $url))
            {
                $feed_cls = $cls;
                break;
            }
        }
               
        if (isset($headers['Content-Type']))
        {            
            $feed = $feed_cls::try_new_from_content_type($headers['Content-Type']);            
            if ($feed)
            {    
                Zend::load('Zend_Feed_Reader');
                $feed = Zend_Feed_Reader::importString($res);
                
                $feed->url = $feed->getLink();
                $feed->feed_url = $url;
            }
            else if (strpos($headers['Content-Type'], 'text/html') !== false)
            {
                $feed = $feed_cls::try_new_from_html($response->content, $url);            
            }
        }
        
        return $feed;
    }
    
    static function try_new_from_content_type($content_type)
    {
        $feed_content_types = array('application/atom+xml', 'application/rss+xml');
        foreach ($feed_content_types as $feed_content_type)
        {
            if (strpos($content_type, $feed_content_type) !== false)
            {
                $cls = get_called_class();
                return new $cls();
            }
        }
        return null;
    }
        
    static function try_new_from_html($html, $url)
    {
        Zend::load('Zend_Feed_Reader_FeedSet');
        
        $dom = new DOMDocument;
        $status = @$dom->loadHTML($html);                
        if ($status)
        {
            $feedSet = new Zend_Feed_Reader_FeedSet();
            $links = $dom->getElementsByTagName('link');
            $feedSet->addLinks($links, $url);
            foreach ($feedSet as $feedItem)
            {
                $feed = static::try_new_from_content_type($feedItem['type']);
                if ($feed) 
                {
                    $feed->url = $url;
                    $feed->feed_url = $feedItem['href'];
                    return $feed;
                }
            }
        }
        return null;
    }    
    
}
<?php

class DiscussionTopic extends Entity
{
    static $table_name = 'discussion_topics';
    
    static $table_attributes = array(
        'subject' => '',
        'language' => '',
        'first_message_guid' => 0,
        'num_messages' => 0,
        'last_time_posted' => 0,
        'last_from_name' => 0,
        'snippet' => '',
    );    
    
    function get_first_message()
    {
        return DiscussionMessage::get_by_guid($this->first_message_guid);
    }
    
    function refresh_attributes()
    {
        $lastMessage = $this->query_messages()->order_by('time_posted desc')->limit(1)->get();
    
        $this->num_messages = $this->query_messages()->count();
        $this->last_time_posted = $lastMessage ? $lastMessage->time_posted : 0;
        $this->last_from_name = $lastMessage ? $lastMessage->from_name : '';
        $this->snippet = $lastMessage ? Markup::get_snippet($lastMessage->content) : '';
    }
    
    function get_url()
    {
        return "{$this->get_container_entity()->get_url()}/topic/{$this->guid}";
    }   
    
    function get_edit_url()
    {
        return "{$this->get_url()}/edit";
    }
    
    function query_messages()
    {
        return DiscussionMessage::query()->where('container_guid = ?', $this->guid)->order_by('time_posted');
    }
}
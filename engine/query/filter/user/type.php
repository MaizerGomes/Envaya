<?php

class Query_Filter_User_Type extends Query_Filter_Select
{
    static function get_name()
    {
        return "User Type";
    }
    
    function get_options()
    {
        return array(
            Organization::get_subtype_id() => "Organization",
            Person::get_subtype_id() => "Person",
        );
    }        
    
    static function get_empty_option()
    {
        return "All user types";
    }              
    
    function _apply($query)
    {
        return $query->where('subtype_id = ?', $this->value);
    }
}
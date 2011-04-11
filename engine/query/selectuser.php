<?php

/*
 * Represents a select query for an User subclass.
 */
class Query_SelectUser extends Query_SelectEntity
{
    protected $fulltext_query = null;
    protected $sector = null;
    protected $region = null;

    function where_visible_to_user()
    {        
        if (!Session::isadminloggedin())
        {
            $this->where("(approval > 0 || e.guid = ?)", (int)Session::get_loggedin_userid());
        }
        return $this;
    }
    
    function with_sector($sector)
    {    
        $this->sector = $sector;        
        return $this;
    }
    
    function with_region($region)
    {       
        $this->region = $region;
        return $this;
    }
    
    function in_area($latMin, $longMin, $latMax, $longMax)
    {
        $this->where("latitude >= ?", $latMin);
        $this->where("latitude <= ?", $latMax);
        $this->where("longitude >= ?", $longMin);
        $this->where("longitude <= ?", $longMax);    
        return $this;
    }

    function fulltext($name)
    {
        // sector and region have to be set before calling fulltext()    
        $this->fulltext_query = $name;    
        return $this;        
    }

    protected function finalize_query()
    {
        parent::finalize_query();
    
        if (!$this->fulltext_query)
        {
            if ($this->sector)
            {
                $this->join("INNER JOIN org_sectors s ON s.container_guid = e.guid");
                $this->where("s.sector_id=?", $this->sector);        
            }
            
            if ($this->region)
            {
                $this->where('region = ?', $this->region);
            }
        }
        else
        {
            $sphinx = Sphinx::get_client();
            $sphinx->setMatchMode(SPH_MATCH_ANY);
            $sphinx->setLimits(0,30);
            $sphinx->setConnectTimeout(5);
            $sphinx->setMaxQueryTime(3);
            
            if ($this->sector)
            {
                $sphinx->setFilter('sector_id', array($this->sector));
            }
            if ($this->region)
            {
                $sphinx->setFilter('region', array($this->region));
            }
            
            $results = $sphinx->query($this->fulltext_query, 'orgs');
            
            if (!$results)
            {
                throw new IOException("Error connecting to search service");
            }            
            
            $matches = @$results['matches'];
                        
            if (!is_array($matches) || sizeof($matches) == 0)
            {
                $this->where('0 > 1'); // force an empty result set; can't use Query_Empty here
            }
            else
            {                   
                $org_guids = array_keys($matches);
                $sql_guids = implode(',',$org_guids);
             
                $this->where("e.guid in ($sql_guids)");
                $this->order_by("FIND_IN_SET(e.guid, '$sql_guids')", true);        
            }
        }
    }       
}
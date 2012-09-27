<?php

class Action_Admin_ActivateFeaturedSite extends Action
{
    protected $featuredSite;

    function before()
    {
        Permission_EditMainSite::require_for_root();

        $guid = Input::get_string('guid');
        $featuredSite = FeaturedSite::get_by_guid($guid);
        if (!$featuredSite)
        {
            throw new NotFoundException();
        }
        $this->featuredSite = $featuredSite;        
    }
     
    function process_input()
    {
        $featuredSite = $this->featuredSite;
        
        $activeSites = FeaturedSite::query()->where('active<>0')->filter();
        
        $featuredSite->active = 1;
        $featuredSite->save();
        
        foreach ($activeSites as $activeSite)
        {
            $activeSite->active = 0;
            $activeSite->save();
        }
        $this->redirect('/org/featured');
    }
}    
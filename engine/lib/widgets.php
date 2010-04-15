<?php

class Widget extends ElggObject
{
    static $subtype_id = T_widget;
    static $table_name = 'widgets';
    static $table_attributes = array(
        'widget_name' => 0,
        'content' => '',
        'data_types' => 0,
    );        
    
    static function getAvailableNames()
    {
        return array('home', 'news', 'projects', 'history', 'team', 'partnerships', 'contact');
    }
    
    function renderView()
    {
        $res = elgg_view("widgets/{$this->widget_name}_view", array('widget' => $this));
        if ($res)
        {
            return $res;
        }    
        return elgg_view("widgets/generic_view", array('widget' => $this));    
    }
    
    function renderEdit()
    {
        $res = elgg_view("widgets/{$this->widget_name}_edit", array('widget' => $this));
        if ($res)
        {
            return $res;
        }    
        return elgg_view("widgets/generic_edit", array('widget' => $this));
    } 
    
    function getURL()
    {
        $org = $this->getContainerEntity();
        
        if ($this->widget_name == 'home')
        {
            return $org->getURL();
        }
        else
        {
            return "{$org->getURL()}/{$this->widget_name}";
        }    
    }
    
    function getEditURL()
    {
        return "{$this->getURL()}/edit";
    }
    
    function saveInput()
    {
        $fn = "save_widget_{$this->widget_name}";
        if (!is_callable($fn))
        {
            $fn = "save_widget";
        }
        $fn($this);
    }    

    public function getImageFile($size = '')
    {
        $file = new ElggFile();
        $file->owner_guid = $this->container_guid;
        $file->setFilename("widget/{$this->guid}$size.jpg");
        return $file;       
    }
    
    public function hasImage()
    {
        return ($this->data_types & DataType::Image) != 0;
    }   
    
    public function getImageURL($size = 'large')
    {
        return $this->hasImage() ? ($this->getImageFile($size)->getURL()."?{$this->time_updated}") : "";
    }

    static function getImageSizes()
    {
        return array(
            'small' => '100x150',
            'medium' => '200x300',
            'large' => '450x450',
        );
    }   

    public function isActive()
    {
        return $this->guid && $this->isEnabled();
    }
}

function save_widget($widget)
{
    $widget->content = get_input('content');
    $widget->image_position = get_input('image_position');
    $widget->save();
    
    $imageFiles = get_uploaded_files('image');

    if (get_input('deleteimage'))
    {
        $widget->setImages(null);
    }    
    else if ($imageFiles)
    {            
        $widget->setImages($imageFiles);        
    }    
}

function save_widget_home($widget)
{
    $org = $widget->getContainerEntity();    
    $org->setSectors(get_input_array('sector'));
    $org->sector_other = get_input('sector_other');
    
    $org->latitude = get_input('org_lat');
    $org->longitude = get_input('org_lng');    
    
    $org->region = get_input('region');
    $org->city = get_input('city');    
    
    $org->save();

    $widget->content = get_input('content');
    $widget->included = get_input_array('included');    
    $widget->zoom = get_input('map_zoom');    
    $widget->save();
    
    save_icon_settings($org);

}

function save_icon_settings($org)
{
    $iconFiles = get_uploaded_files('icon');

    if (get_input('deleteicon'))
    {
        $org->setIcon(null);       
        system_message(elgg_echo("org:icon:reset"));
    }
    else if ($iconFiles)
    {
        $org->setIcon($iconFiles);
    }   
}

function save_widget_contact($widget)
{
    $org = $widget->getContainerEntity();
    $widget->public_email = get_input('public_email');
    $org->phone_number = get_input('phone_number');    
    $org->contact_name = get_input('contact_name');    
    $org->contact_title = get_input('contact_title');    
    $org->street_address = get_input('street_address');
    $org->mailing_address = get_input('mailing_address');
    $org->save();
    $widget->save();
}

function save_widget_partnerships($widget)
{
    $org = $widget->getContainerEntity();
    $partnerships = $org->getPartnerships();
    
    foreach($partnerships as $p)
    {
        $p->description = get_input("partnershipDesc{$p->guid}");
        $p->save();
    }
    $widget->save();
}

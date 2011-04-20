<?php

class Action_AddPhotos extends Action
{
    function before()
    {
        $this->require_org();
        $this->require_editor();
    }
     
    function process_input()
    {
        $imageNumbers = get_input_array('imageNumber');
        
        $uniqid = get_input('uniqid');
        $org = $this->get_org();
        
        $duplicates = NewsUpdate::query()->with_metadata('uniqid', $uniqid)->where('container_guid=?',$org->guid)->filter();
        
        foreach ($imageNumbers as $imageNumber)
        {                        
            $imageData = get_input('imageData'.$imageNumber);
            
            if (!$imageData) // mobile version uploads image files when the form is submitted, rather than asynchronously via javascript
            {     
                $sizes = json_decode(get_input('sizes'));
                $images = UploadedFile::upload_images_from_input($_FILES['imageFile'.$imageNumber], $sizes);
            }
            else
            {
                $images = UploadedFile::json_decode_array($imageData);
            }
            
            $imageCaption = get_input('imageCaption'.$imageNumber);
            
            $image = $images[sizeof($images) - 1];
            
            $body = "<p><img class='image_center' src='{$image->get_url()}' width='{$image->width}' height='{$image->height}' /></p>";
            if ($imageCaption)
            {
                $body .= "<p>".view('input/longtext', array('value' => $imageCaption))."</p>";
            }
                        
            $post = new NewsUpdate();
            $post->owner_guid = Session::get_loggedin_userid();
            $post->container_guid = $org->guid;
            $post->set_content($body);
            $post->set_metadata('uniqid', $uniqid);
            $post->save();              
            $post->post_feed_items();
        }
        
        SessionMessages::add(__('addphotos:success'));
        forward($org->get_url()."/news");
    }

    function render()
    {
        $org = $this->get_org();
        
        $this->page_draw(array(
            'title' => __('addphotos:title'),
            'content' => view('org/add_photos', array('org' => $org))
        ));        
    }
    
}    
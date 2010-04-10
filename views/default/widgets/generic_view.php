<div class='padded section_content'>
<?php
    $widget = $vars['widget'];
    
    if ($widget->hasImage())
    {
        $imagePos = $widget->image_position;
        $imageSize = ($imagePos == 'left' || $imagePos == 'right') ? 'medium' : 'large';
        
        $img = "<img class='widget_image_".escape($imagePos)."' src='{$widget->getImageUrl($imageSize)}' />";                    
        
        if ($imagePos != 'bottom')
        {
            echo $img;
        }
    }    
    
    echo view_translated($widget, 'content');
    
    if ($widget->hasImage() && $imagePos == 'bottom')
    {
        echo $img;
    }
?>
<div style='clear:both'></div>
</div>
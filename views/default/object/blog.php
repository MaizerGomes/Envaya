<?php
  
    $entity = $vars['entity'];
    $url = $entity->getURL();    
    $full = $vars['full'];
    
    $nextUrl = $url . "/next";
    $prevUrl = $url . "/prev";        
    
    if (!$full)
    {
        echo "<div class='blog_post_wrapper padded'>";
    }
?>
   
<div class="blog_post">    
    <?php 

        if ($entity->hasImage())
        {
            $imageSize = ($full) ? 'large' : 'small';        
            $imgLink = ($full) ? $nextUrl : $url;
            echo "<a class='{$imageSize}BlogImageLink' href='$imgLink'><img src='{$entity->getImageURL($imageSize)}' /></a>";            
        }
        
        echo elgg_view('output/longtext', array('value' => translate_field($entity, 'content'))); 
        
        echo "<div class='blog_date'>{$entity->getDateText()}</div>";
    ?>              
    <div style='clear:both'></div>
</div>

<?php 

    if (!$full)
    {
        echo "</div>";
    }
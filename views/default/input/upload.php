<?php
    global $UPLOAD_INCLUDE_COUNT;
    if (!isset($UPLOAD_INCLUDE_COUNT))
    {
        $UPLOAD_INCLUDE_COUNT = 0;
    }
    else
    {
        $UPLOAD_INCLUDE_COUNT++;
    }
    
    $resultId = @$vars['internalid'] ?: "upload_result$UPLOAD_INCLUDE_COUNT";
    $progressId = "upload_progress$UPLOAD_INCLUDE_COUNT";
?>

<span id='upload_span<?php echo $UPLOAD_INCLUDE_COUNT; ?>'><?php
    $value = json_decode(@$vars['value'], true);
    $has_value = $value && isset($value['original']);
    
    if ($has_value)
    {
        $original = $value['original'];        
        echo "<a target='_blank' href='".escape($original['url'])."'>".escape($original['filename'])."</a>";
    }
?></span>
<span id='upload_remove_span<?php echo $UPLOAD_INCLUDE_COUNT; ?>' <?php 
    if (!$has_value) { echo "style='display:none'"; } 
?> >
<input type='button' onclick='removeUpload<?php echo $UPLOAD_INCLUDE_COUNT ?>();' value='<?php echo escape(__('upload:remove')); ?>' />
</span>

<span id='upload_browse_span<?php echo $UPLOAD_INCLUDE_COUNT; ?>' <?php 
    if ($has_value) { echo "style='display:none'"; } 
?>>
<?php
    echo view('input/swfupload', array(
        'internalname' => $vars['internalname'],
        'internalid' => $resultId,
        'progressid' => $progressId,
        'value' => @$vars['value'],
        'swfupload_class' => 'FileUploader',
        'swfupload_args' => array(
            'recommend_flash_message' => '',
            'upload_progress_message' =>  __('upload:uploading'),
            'upload_error_message' => __('upload:error'),
            
        ),
        'jsname' => 'uploader'
    ));
?>
</span>
<script type='text/javascript'>

    var uploader = window.uploader;
    
    uploader.showParsedPreviewImage = function($images, $serverData) 
    {
        var span = document.getElementById('upload_span<?php echo $UPLOAD_INCLUDE_COUNT; ?>');
        removeChildren(span);
        var link = document.createElement('a');
        link.target = "_blank";
        link.href = $images.original.url;
        link.appendChild(document.createTextNode($images.original.filename));
        span.appendChild(link);
            
        var removeSpan = document.getElementById('upload_remove_span<?php echo $UPLOAD_INCLUDE_COUNT; ?>');
        removeSpan.style.display = 'inline';

        var browseSpan = document.getElementById('upload_browse_span<?php echo $UPLOAD_INCLUDE_COUNT; ?>');
        browseSpan.style.display = 'none';        
        
        document.getElementById('<?php echo $resultId; ?>').value = $serverData;
    };
    
    function removeUpload<?php echo $UPLOAD_INCLUDE_COUNT ?>()
    {
        var removeSpan = document.getElementById('upload_remove_span<?php echo $UPLOAD_INCLUDE_COUNT; ?>');
        removeSpan.style.display = 'none';

        var browseSpan = document.getElementById('upload_browse_span<?php echo $UPLOAD_INCLUDE_COUNT; ?>');
        browseSpan.style.display = 'inline';
        
        var span = document.getElementById('upload_span<?php echo $UPLOAD_INCLUDE_COUNT; ?>');
        removeChildren(span);
        
        document.getElementById('<?php echo $resultId; ?>').value = '';
    }
    
    document.getElementById('<?php echo $progressId; ?>').style.display = 'inline';
</script>
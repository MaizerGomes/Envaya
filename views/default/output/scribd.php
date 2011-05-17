<?php
    $docid = $vars['docid'];
    $accesskey = $vars['accesskey'];
    $filename = $vars['filename'];
    
    $ext = pathinfo($filename, PATHINFO_EXTENSION);    
    $is_presentation = in_array($ext, array('ppt','pptx','odp'));
        
    if (!$INCLUDE_COUNT)
    {
        echo "<script type='text/javascript' src='http://www.scribd.com/javascripts/view.js'></script>";
    }
?>
<div id='scribd<?php echo $INCLUDE_COUNT; ?>'><?php echo view('output/scribd_link', $vars); ?></div>
<script type="text/javascript">
(function() {
    var doc = scribd.Document.getDoc(<?php echo (int)$docid ?>, <?php echo json_encode((string)$accesskey) ?>);
    doc.addParam('jsapi_version', 1);
    <?php if ($is_presentation) { ?>
    doc.addParam('mode','slide');
    doc.addParam('height','490');
    <?php } else { ?>
    doc.addEventListener('iPaperReady', function(e){
        doc.api.setZoom(1);
    });
    <?php } ?>
    doc.write('scribd<?php echo $INCLUDE_COUNT; ?>');
})();
</script>

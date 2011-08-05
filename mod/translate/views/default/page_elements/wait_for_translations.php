<?php
    $waiting_message = $vars['waiting_message'];
    $error_message = $vars['error_message'];
    $unsaved_translations = $vars['unsaved_translations'];
    
    echo "<span id='translate_status'>$waiting_message</span>";
?>
<script type='text/javascript'>
<?php echo view('js/xhr'); ?>

(function() {
    var checkCount = 0;

    var xhr = getXHR(function(res) {
        if (res.has_translation)
        {
            window.location.reload();
        }
        else if (checkCount < 15)
        {
            setTimeout(checkTranslation, 500);
        }
        else
        {
            var translateStatus = $('translate_status');
            translateStatus.innerHTML = <?php echo json_encode($error_message); ?>;
        }
    });

    function checkTranslation()
    {
        checkCount++;
    
        asyncPost(xhr, '/tr/check_translation', {keys: <?php
            echo json_encode(implode(',',
                array_map(function($t) { return $t->get_container_entity()->guid; }, $unsaved_translations)
            ));
        ?>});
    }
    setTimeout(checkTranslation, 500);
})();

</script>
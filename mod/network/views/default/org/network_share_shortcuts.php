<?php
    $org = $vars['org'];
    $network = $org->get_widget_by_class('Network');
?>

<script type='text/javascript'>
function addPartners()
{
    fetchJson('/<?php echo $org->username; ?>/network/x/relationship_emails_js?t=' + (new Date().getTime()), function(res) {
        var emails = res.emails;
        var input = document.forms[0].emails;
        var added = false;
        
        for (var i = 0; i < emails.length; i++)
        {
            var email = emails[i];
            if (!isRecipient(email))
            {
                addRecipient(email);
                added = true;
            }
        }
        if (!added)
        {
            var modalBox = createModalBox({
                title: <?php echo json_encode(__('share:no_partners')); ?>, 
                content: createElem('div', {className:'padded'}, 
                    <?php echo json_encode(__('share:no_partners_2')); ?>
                ),
                okFn: function() { 
                    removeElem(modalBox);
                    window.open(<?php echo json_encode($network->get_edit_url()); ?>);
                },
                focus: true
            });            
            document.body.appendChild(modalBox);              
        }
    });
}
</script>

&middot;
<a href='javascript:addPartners()' id='add_partners' onclick='ignoreDirty()'><?php echo __('share:add_partners'); ?></a>
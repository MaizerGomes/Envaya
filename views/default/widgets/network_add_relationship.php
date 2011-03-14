<?php
    $widget = $vars['widget'];
    $action = $vars['action'];
    $header = $vars['header'];
    $name_label = @$vars['name_label'] ?: __('network:search_name');
    $can_add_unregistered = $vars['can_add_unregistered'];
    $confirm = $vars['confirm'];
    $not_shown = $vars['not_shown'];
    
    ob_start();
?>

<script type='text/javascript'>
<?php echo view('js/create_modal_box'); ?>
var modalBox;

function searchOrg()
{
    var query = {
        'name': document.getElementById('name').value,
        'email': document.getElementById('email').value,
        'website': document.getElementById('website').value
    };   
    
    if (!query.name && !query.email && !query.website)
    {
        alert(<?php echo json_encode(__('network:blank_member')); ?>);
        return;
    }    
        
    var searching = document.getElementById('searching_message');
    searching.style.display = 'block';        
        
    fetchJson('/org/js_search?name='+encodeURIComponent(query.name)+
            '&email='+encodeURIComponent(query.email)+
            '&website='+encodeURIComponent(query.website), 
        function(res) {        
            closeDialog();
        
            searching.style.display = 'none';
            
            var content = createElem('div', {className:'padded'});            
            var results = res.results || [];
            
            if (results.length == 0)
            {
                showNotFoundDialog(query);            
            }            
            else 
            {
                showConfirmMemberDialog(query, results);
            }                       
        }
    );      
}

function addNewOrg(invite)
{
    document.getElementById('org_guid').value = '';
    document.getElementById('invite').value = invite ? '1' : '';
    document.forms[0].submit();
}

function addExistingOrg(org)
{
    document.getElementById('org_guid').value = org.guid;
    document.forms[0].submit();
}

function closeDialog()
{
    if (modalBox)
    {
        removeElem(modalBox);
        modalBox = null;
    }
}

function showNotFoundDialog(query)
{
    var content = createElem('div', {className:'padded'},
        <?php echo json_encode(__('network:org_not_registered')); ?>.replace("%s",query.name||query.email||query.website)
    );        
    
    if (query.email)
    {
        var invite = createElem('input', { type: 'checkbox', id: 'invite_box', checked: 'checked', defaultChecked: 'checked' });
        
        content.appendChild(createElem('div',
            createElem('label', { 'for': 'invite_box' },
                invite,
                <?php echo json_encode(__('network:invite_org')); ?>.replace("%s",query.email)
            )
        ));
    }
    else
    {
        content.appendChild(createElem('div',
            <?php echo json_encode($can_add_unregistered); ?>
        ));
    }
    
    document.body.appendChild(modalBox = createModalBox({
        title: <?php echo json_encode($header); ?>, 
        content: content,
        okFn: function() { closeDialog(); addNewOrg(invite && invite.checked); },
        hideCancel: true,
        focus: true
    }));
}

function getOrgResultView(result)
{
    return createElem('div',
        createElem('div', {className:'selectMemberButton'}, 
            createElem('input', {
                type:'submit',                 
                click: function() { closeDialog(); addExistingOrg(result.org); },
                value:<?php echo json_encode(__('network:add_select')); ?>+" \xbb"
            })
        ),
        createElem('div', {innerHTML:result.view})
    );
}

function showConfirmMemberDialog(query, results)
{
    var content = createElem('div', {className:'padded'});        
    content.appendChild(createElem('div', <?php echo json_encode($confirm); ?>));
                  
    for (var i = 0; i < results.length; i++)
    {       
        content.appendChild(getOrgResultView(results[i]));
    }       
    
    content.appendChild(createElem('div',
        createElem('hr'),                    
        createElem('a', {
                href:'javascript:void(0)', 
                click:function() { ignoreDirty(); closeDialog(); showNotFoundDialog(query); }, 
                className:'selectMemberNotShown'
            }, 
            <?php echo json_encode($not_shown); ?>)
    ));
    
    document.body.appendChild(modalBox = createModalBox({
        title: <?php echo json_encode($header); ?>, 
        content: content,
        hideOk: true,
        hideCancel: true,
        focus: true
    }));                    
}

</script>

<form method='POST' action='<?php echo $widget->get_edit_url() ?>?action=<?php echo $vars['action']; ?>'>
<div class='instructions'>
<?php echo $vars['instructions']; ?>
</div>
<?php

echo view('input/securitytoken');
echo view('input/hidden', array('name' => 'org_guid', 'id' => 'org_guid'));
echo view('input/hidden', array('name' => 'invite', 'id' => 'invite')); 

?>

<table class='inputTable' style='margin:0 auto'>
<tr><th><?php echo $name_label; ?></th>
<td><?php echo view('input/text', array('name' => 'name', 'id' => 'name')); ?></td></tr>
<tr><th><?php echo __('network:search_email'); ?></th>
<td><?php echo view('input/text', array('name' => 'email', 'id' => 'email')); ?></td></tr>
<tr><th><?php echo __('network:search_website'); ?></th>
<td><?php echo view('input/text', array('name' => 'website', 'id' => 'website')); ?></td></tr>
<tr><th>&nbsp;</th>
<td>
<div id='searching_message' style='display:none;float:right;padding-top:18px'><?php echo __('network:searching'); ?></div>
<?php echo view('input/submit', array(
    'name' => '_save',
    'value' => __('network:search_button'),
    'js' => "onclick='searchOrg(); return false;'"
));
?>
</td></tr>
</table>    
</form>

<?php echo view('focus', array('name' => 'name')); ?>

<?php
    $content = ob_get_clean();
    
    echo view('section', array('header' => $header, 'content' => $content));
?>
<div class='section_content padded'>
<?php
    $user = $vars['user'];
    $url = $vars['url'];    

    $is_site_editor = Permission_EditUserSite::has_for_entity($user);
    
    echo view('js/create_modal_box');
    echo view('js/dom');
    echo view('js/xhr');
?>
<script type='text/javascript'>

function isRecipient(email)
{
    var input = document.forms[0].emails;
    return input.value.toLowerCase().indexOf(email.toLowerCase()) != -1;
}

function addRecipient(email)
{
    var input = document.forms[0].emails;
    if (input.value)
    {
        input.value += "; ";
    }
    input.value += email;
}

function removeRecipient(email)
{
    var input = document.forms[0].emails;
    var value = input.value;
    
    var index = value.toLowerCase().indexOf(email.toLowerCase());
    if (index != -1)
    {
        input.value = value.substring(0,index) + value.substring(index + email.length + 1);
    }
}
</script>

<form method='POST' action="<?php echo $user->get_url(); ?>/share">
<?php echo view('input/securitytoken'); ?>

<div class='input'>
<label><?php echo __('share:email_label'); ?></label><br />
<?php
    echo view('input/longtext', array(
        'name' => 'emails',
        'track_dirty' => true,
        'style' => "height:40px",
        'value' => '',
    ));

    if ($is_site_editor)
    {
        echo view('account/share_shortcuts', array('user' => $user));
    } 
?>
</div>

<div class='input'>
<label><?php echo __('share:subject_label'); ?></label><br />
<?php
    echo view('input/text', array(
        'name' => 'subject',
        'track_dirty' => true,
        'value' => $is_site_editor ? strtr(__('share:subject'), array(
            '{name}' => $user->name, 
        )) : '',
    ));
?>
</div>


<div class='input'>
<label><?php echo __('message:message'); ?></label><br />
<?php
    echo view('input/longtext', array(
        'name' => 'message',
        'track_dirty' => true,
        'value' => '',
    ));
?>
<div>
<?php echo __('share:link').' '; ?>
<?php echo escape($url); ?>
</div>
</div>
<?php
    echo view('input/hidden', array(
        'name' => 'u',
        'value' => $url,
    ));    
    
    echo view('focus', array('name' => 'emails'));
    echo view('input/submit', array('value' => __('message:send')));
?>
</div>
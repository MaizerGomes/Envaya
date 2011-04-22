<div class='padded'>
<?php 
    $orgs = $vars['orgs'];
    $email = $vars['email'];
    
    $org = $orgs[0];
    if ($org) {
?>

<form action='/admin/send_email' method='POST'>

<?php echo view('input/securitytoken'); ?>
To:
<div style='<?php echo (sizeof($orgs) > 5) ? "height:150px;overflow:auto;" : ''; ?>font-size:10px'>
<?php
    
    $options = array();
    foreach ($orgs as $org)
    {
        $options[$org->guid] = $org->get_name_for_email();
    }

 echo view('input/checkboxes', array(
    'name' => 'orgs',
    'options' => $options,
    'value' => array_keys($options)
 ));
?>
</div>
<br />

<?php 
    echo view('admin/preview_email', array('email' => $email, 'org' => $org));
?>

<?php
    echo view('input/hidden',array(
        'name' => 'email',
        'value' => $email->guid
    ));
    
    echo view('input/hidden',array(
        'name' => 'from',
        'value' => get_input('from')
    ));
    
    echo view('input/submit',array(
        'value' => __('message:send')
    ));

    }
?>
</div>
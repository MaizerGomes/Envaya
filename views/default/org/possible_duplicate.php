<div class='section_content padded'>

<form method='POST' action='<?php echo escape($_SERVER['REQUEST_URL']); ?>'>

<div class='instructions'>
<?php echo __('register:duplicate_instructions'); ?>
</div>

<?php

foreach ($vars['duplicates'] as $dup)
{
    $link_start = "<a href='".url_with_param($vars['login_url'],'username',$dup->username)."'>";
    $link_end = "</a>";

    echo view('search/listing', array(
        'icon' => $link_start.view('org/icon', array('org' => $dup)).$link_end,
        'info' => $link_start.escape($dup->name).
            "<br /><span style='font-size:10px'>".escape($dup->get_location_text()).
            "<br />Username: {$dup->username}</span>".$link_end
    ));
}

?>

<div class='instructions'>
<?php echo __('register:not_duplicate_instructions'); ?>
</div>

<?php

echo view('input/hidden', array('name' => 'ignore_possible_duplicates', 'value' => '1'));

echo view('input/hidden_multi', array('fields' => $_POST));

echo view('input/submit', array('value' => __('register:not_duplicate')));

?>
</form>
</div>	
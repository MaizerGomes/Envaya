<div class='section_content padded'>

<div class='instructions'>
<?php echo $vars['instructions']; ?>
</div>
<form method='POST' action='<?php echo escape($_SERVER['REQUEST_URL']); ?>'>

<?php

echo view('input/hidden_multi', array('fields' => $_POST));

echo view('input/hidden', array('name' => 'captcha', 'value' => '1'));
echo Recaptcha::get_html();

echo view('input/submit', array('value' => __('captcha:button')));

?>

</form>
</div>	
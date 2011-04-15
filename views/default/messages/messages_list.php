<?php

	/**
	 * Lists system messages
	 * @uses $vars['object'] An array of system messages
	 */

	if (!empty($vars['object']) && is_array($vars['object'])) {
        $id = "message_container{$vars['include_count']}";

?>
    <div class='message_container' id='<?php echo $id; ?>'>
	<div class="good_messages">
        <a class='hideMessages' href='javascript:hideMessages("<?php echo $id; ?>")' onclick='ignoreDirty()'></a>
<?php		
        foreach($vars['object'] as $message) {
            echo "<p>$message</p>";
        }
?>
	</div>
    </div>
	
<?php

	}

?>

<?php

	/**
	 * Displays a single exception
	 * @uses $vars['object'] An exception
	 */

	$class = get_class($vars['object']);
	$message = view('output/longtext', array('value' => $vars['object']->getMessage()));
	
	$body = <<< END
		<span title="$class">
			<b>$message</b>
		</span>
END;

	if (Config::get('debug'))
	{
		$details = view('output/longtext', array('value' => print_r($vars['object'], true)));
		$body .= <<< END
		<hr />
		<p class="messages-exception-detail">
			$details
		</p>
END;
	}
	
	$title = $class;
	
	echo view_layout("one_column_padded", view_title($title), $body);
?>
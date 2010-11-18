<?php

    /**
     * Displays an email input field
     *
     * @uses $vars['value'] The current value, if any
     * @uses $vars['js'] Any Javascript to enter into the input tag
     * @uses $vars['internalname'] The name of the input field
     *
     */

    $class = @$vars['class'];
    if (!$class) $class = "input-text";

    $value = restore_input($vars['internalname'], @$vars['value']);
?>

<input type="text" <?php echo @$vars['js']; ?> name="<?php echo $vars['internalname']; ?>" <?php if (isset($vars['internalid'])) echo "id=\"{$vars['internalid']}\""; ?>value="<?php echo escape($value); ?>" class="<?php echo $class; ?>"/>
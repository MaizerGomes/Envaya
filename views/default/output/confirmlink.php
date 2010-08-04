<?php

    /**
     * Elgg confirmation link
     * A link that displays a confirmation dialog before it executes
     *
     * @package Elgg
     * @subpackage Core

     * @author Curverider Ltd

     * @link http://elgg.org/
     *
     * @uses $vars['text'] The text of the link
     * @uses $vars['href'] The address
     * @uses $vars['confirm'] The dialog text
     *
     */

    $confirm = @$vars['confirm'];
    if (!$confirm)
        $confirm = __('question:areyousure');

    $link = $vars['href'];

    if ($vars['is_action'])
    {
        $ts = time();
        $token = generate_security_token($ts);

        $sep = "?";
        if (strpos($link, '?')>0) $sep = "&";
        $link = "$link{$sep}__token=$token&__ts=$ts";
    }

    if (@$vars['class']) {
        $class = 'class="' . $vars['class'] . '"';
    } else {
        $class = '';
    }
?>
<a href="<?php echo $link; ?>" <?php echo $class; ?> onclick="return confirm('<?php echo addslashes($confirm); ?>');"><?php echo escape($vars['text']); ?></a>
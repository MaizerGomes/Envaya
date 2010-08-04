<?php

    /**
     * Elgg list system messages
     * Lists system messages
     * 
     * @package Elgg
     * @subpackage Core

     * @author Curverider Ltd

     * @link http://elgg.org/
     * 
     * @uses $vars['object'] An array of system messages
     */

    if (!empty($vars['object']) && is_array($vars['object'])) {

?>

    <div class="message_container">
    <div class="bad_messages">
<?php
        
            foreach($vars['object'] as $message) {
                echo view('messages/messages/message',array('object' => $message));
            }

?>

    </div>
    </div>
<?php

    }

?>

<?php
    /**
     * CSRF security token view for use with secure forms.
     *
     * It is still recommended that you use input/form.
     *
     * @package Elgg
     * @subpackage Core
     * @author Curverider Ltd
     * @link http://elgg.org/
     */

    $ts = time();
    $token = generate_security_token($ts);

    echo view('input/hidden', array('internalname' => '__token', 'value' => $token));
    echo view('input/hidden', array('internalname' => '__ts', 'value' => $ts));
?>

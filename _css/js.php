<?php
    global $viewinput;
    $viewinput['view'] = 'js/' . $_GET['js'];
    $viewinput['viewtype'] = $_GET['viewtype'];

    header('Content-type: text/javascript');
    header('Expires: ' . date('r',time() + 864000000));
    header("Pragma: public");
    header("Cache-Control: public");

    require_once(dirname(__DIR__) . '/simplecache/view.php');
?>
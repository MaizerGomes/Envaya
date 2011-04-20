<?php
    $org = $vars['org'];

    ob_start();

    echo view('input/tinymce',
        array(
            'name' => 'blogbody',
            'id' => 'post_rich',
            'autoFocus' => true,
            'trackDirty' => true
        )
    );

    echo view('input/submit',
        array(
            'class' => "submit_button addUpdateButton",
            'value' => __('publish')));

    echo view('input/hidden', array(
        'name' => 'uniqid',
        'value' => uniqid("",true)
    ));

    echo view('news/attach_image');

    $formBody = ob_get_clean();

    echo view('input/form', array(
        'id' => 'addPostForm',
        'action' => "{$org->get_url()}/post/new",
        'enctype' => "multipart/form-data",
        'body' => $formBody,
    ));

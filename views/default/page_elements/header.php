<?php

    /**    
     * The standard HTML header that displays across the site
     
     * @uses $vars['title'] The page title
     * @uses $vars['body'] The main content of the page
     */

     // Set title
        $sitename = @$vars['sitename'] ?: Config::get('sitename');
     
        if (empty($vars['title'])) {
            $title = $sitename;
        } else {
            $title = $sitename . ": " . $vars['title'];
        }

    echo view('page_elements/doctype');
    
    $lang = escape(get_language());
        
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang ?>" lang="<?php echo $lang ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo escape($title); ?></title>
    <base href='<?php echo Request::$protocol == 'https' ? Config::get('secure_url') : Config::get('url'); ?>' />     

    <?php
        echo view('page_elements/css', $vars);          
        if (PageContext::has_rss())
        {
            echo '<link rel="alternate" type="application/rss+xml" title="RSS" '.
                'href="'.escape(url_with_param(Request::full_original_url(), 'view', 'rss')).'" />';
        }   
        
        echo "<link rel='canonical' href='".escape(Request::canonical_url())."' />";
    ?>
    <link rel="shortcut icon" href="/_graphics/favicon2.ico" />
<script type='text/javascript'>
var __ = <?php echo json_encode(array('page:dirty' => __("page:dirty"))); ?>;
<?php echo view('js/header'); ?>
</script>
    <?php echo PageContext::get_header_html(); ?>
</head>

<body class='<?php echo @$vars['bodyClass']; ?>'>

<?php if (get_input("__readonly") == "1") { ?>
<div style='position:absolute;background-color:white;width:600px;height:500px;left:0px;top:0px;opacity:0.01;z-index:100;filter:alpha(opacity=1);z-index:100'></div>
<?php } ?>
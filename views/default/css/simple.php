<?php
    $contentWidth = $vars['contentWidth'] = 615;
    
    echo view('css/default', $vars);

    $graphicsDir = "/_media/images/simple";
?>

body
{
    background:#fff;
}

.content_container
{
    background:#fff url("<?php echo $graphicsDir; ?>/bg_gradient.gif") repeat-x left 42px;
}

.thin_column
{
    width:<?php echo $contentWidth - 15 ?>px;
}

#site_menu,
#heading h1
{
    background:#fff url(<?php echo $graphicsDir; ?>/headings2.png) repeat-x left bottom;
    height:36px;
    text-align:center;
    font-size:18px;
    font-weight:bold;
    padding-top:9px;
    color:#333;
}

#heading h1.org_only_heading
{
    background-position:left top;
}


#site_menu
{
    font-size:14px;
    padding-top:14px;
    height:31px;
    margin-top:0px;
    font-weight:normal;
}

#site_menu a.selected
{
    color:black;
}

#site_menu a
{
    color:#333;
}

#content_top
{
    height:17px;
    background:#fff url(<?php echo $graphicsDir; ?>/plate3.png) no-repeat -<?php echo ($contentWidth - 15); ?>px -8px;
}

#content_bottom
{
    height:35px;
    margin-top:-10px;
    background:#fff url(<?php echo $graphicsDir; ?>/plate3.png) no-repeat right bottom;
}

#content_mid
{
    background:#fff url(<?php echo $graphicsDir; ?>/plate3.png) repeat-y -<?php echo 2 * ($contentWidth - 15) ?>px top;
    padding:0px 2px;
}

#heading
{
    font-size:16px;
    padding:10px 0px 0px 0px;
    margin-top:25px;
}

.section_header
{
    height:19px;
    width:213px;
    padding:13px 0px;
    text-align:center;
    font:bold 16px Arial;
}

.section_header
{
    margin:0px auto 4px auto;
}

.heading_green
{
    background-position:left bottom;
}


.view_toggle
{
    padding-bottom:3px;
}

.footer_container
{
    font-size:12px;
    color:#333;
}

.footer_container a
{
    color:#555;
}

.tabs
{
    width:100%;
    margin-bottom:5px;
    margin-top:-10px;
    background:#fff url(<?php echo $graphicsDir; ?>/plate3.png) no-repeat -<?php echo ($contentWidth - 13); ?>px -16px;
}

.tab
{
    height:36px;
    text-align:center;
    border-left:1px solid #ddd;
    border-right:1px solid #ddd;
    border-bottom:1px solid #ddd;
}

.tab span
{
    display:block;
    padding:5px 5px;
    color:black;
}


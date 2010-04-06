<?php
    include(dirname(__FILE__)."/default.php");

    $graphicsDir = $vars['url'] . "_graphics/simple";
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
    width:485px;
}

.simple_heading, #site_menu
{
    background:#fff url("<?php echo $graphicsDir; ?>/headings.gif") repeat-x left bottom;          
    height:37px;
    text-align:center;
    font-size:20px;
    font-weight:bold;
    padding-top:8px;
    color:#333;
}

.simple_heading
{
    margin-top:30px;
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

.org_only_heading
{
    background-position:left top;
}

#content_top
{
    height:17px;
    background:#fff url("<?php echo $graphicsDir; ?>/plate.gif") no-repeat -485px -8px;  
    
}

.home #content_top
{
    height:28px;
    background:#fff url("<?php echo $graphicsDir; ?>/plate.gif") no-repeat left 0px;  
}


#content_bottom
{
    height:35px;
    margin-top:-10px;
    background:#fff url("<?php echo $graphicsDir; ?>/plate.gif") no-repeat right bottom;  
}

#content_mid
{
    background:#fff url("<?php echo $graphicsDir; ?>/plate.gif") repeat-y -970px top;      
    padding:0px 2px;
}

#content_mid .padded
{
    padding-top:0px;
}

#heading
{
    font-size:16px;
    padding:10px 0px 0px 0px;
}

.home #heading
{    
    font-size: 15.5px;
    color: #666;
    font-family: arial;
    letter-spacing: 0.5px;
    padding:20px 10px;
}

.home_heading, .section_header
{
    height:19px;
    width:203px;
    padding:13px 0px;
    text-align:center;
    font:bold 16px Arial;
    background:url("<?php echo $graphicsDir; ?>/home_headings.gif") no-repeat left top;      
}

.home_section a
{
    color:#555;
    margin:8px 5px 8px 5px;
}

.section_header
{
    margin:0px auto 4px auto;    
}

.home_section
{
    background:url("<?php echo $graphicsDir; ?>/home_plate.gif?v2") no-repeat left 28px;      
    width:203px;
    margin:0 auto;
    height:185px;    
}

.heading_green
{
    background-position:left bottom;      
}

.icon_link
{
    background:url(<?php echo $graphicsDir; ?>/homeicons.gif) no-repeat left top;
}

.view_toggle
{
    padding-bottom:3px;
}

.icon_signup            { background-position:left -80px; }
a.icon_signup:hover     { background-position:left -120px; }

.icon_help              { background-position:left 0px; }
a.icon_help:hover       { background-position:left -40px; }

.icon_logout            { background-position:left -160px; }
a.icon_logout:hover     { background-position:left -200px; }

.icon_explore           { background-position:left -240px; }
a.icon_explore:hover    { background-position:left -280px; }

.icon_search            { background-position:left -320px; }
a.icon_search:hover     { background-position:left -360px; }

.icon_feed              { background-position:left -400px; }
a.icon_feed:hover       { background-position:left -440px; }

.footer_container
{
    font-size:12px;
    color:#333;
}

.footer_container a
{
    color:#555;
}
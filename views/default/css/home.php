<?php
    include(__DIR__."/default.php");
    $graphicsDir = "/_graphics/home";
?>


.thin_column
{
    width:882px;
}

#content
{
    background:url(<?php echo $graphicsDir ?>/bg_combined.gif) no-repeat left top;
    margin-top:5px;
    padding-top:27px;
    background-color:#fff;
}

#content_mid
{
    background:url(<?php echo $graphicsDir ?>/bg_combined.gif) repeat-y right top;
    padding-left:31px;
    padding-right:31px;
}

#content_bottom
{
    background:url(<?php echo $graphicsDir ?>/bg_combined.gif) no-repeat -882px bottom;
    height:21px;
}

.home_content_bg
{
}

<?php
    $contentWidth = 820;
    $headerLeftWidth = 340;
    $headerRightWidth = $contentWidth - $headerLeftWidth;
?>

.home_banner
{
    position:relative;
    height:299px;
    width:<?php echo $contentWidth; ?>px;
}

.home_banner_text
{
    position:absolute;
    top:0px;
    left:0px;
    width:<?php echo $headerLeftWidth; ?>px;
    height:299px;
    background:#333;
}

.home_follow_shadow
{
    position:absolute;
    bottom:0px;
    left:<?php echo $headerLeftWidth; ?>px;
    height:48px;
    width:<?php echo $headerRightWidth; ?>px;
    background-color:#000;
    opacity:0.6;
    filter:alpha(opacity=60);
}

.home_donate_sticker
{
    position:absolute;
    bottom:0px;
    left:50px;
    width:234px;
    height:150px;    
    background:url(<?php echo $graphicsDir ?>/donate_sticker.jpg) no-repeat left top;
}

.home_donate_difference, .home_get_website
{
    padding-top:33px;
    padding-left:15px;
    padding-right:20px;
    text-align:center;
    color:#5b7d26;
    font-weight:bold;
    font-size:16px;
}

.home_donate_button
{
    margin-top:15px;
    margin-left:40px;
    display:block;
    width:142px;
    height:43px;
    background:url(<?php echo $graphicsDir ?>/donate.gif) no-repeat left top;    
}

.home_donate_button:hover
{
    text-decoration:none;
    background-position:left -45px;    
}

.home_donate_button span
{
    text-align:center;
    display:block;
    color:white;
    font-weight:bold;
    padding:7px 4px;
    font-size:18px;
}


.home_banner_photo
{
    position:absolute;
    left:<?php echo $headerLeftWidth; ?>px;
    top:0px;
    height:299px;
    width:<?php echo $headerRightWidth; ?>px;
    background-color:#f0f0f0;
}

.home_follow
{
    position:absolute;
    bottom:10px;
    right:115px;
    text-align:right;
    width:300px;
    color:#ccc;
    font-size:20px;
}

.home_follow_icon
{
    position:absolute;
    background:url(<?php echo $graphicsDir ?>/facebook.gif) no-repeat left top;
    display:block;
    width:36px;
    height:36px;
    bottom:5px;    
}

.home_follow_fb
{
    right:65px;    
}

.home_follow_twitter
{
    background-position:-36px top;
    right:20px;
}


.home_table
{
    margin-top:4px;
    width:820px;
}

.home_table td
{
    background-color:white;
}

.home_banner_text em
{
    color: #dbea8f;
    font-weight:bold;
    font-family: Verdana, sans-serif;
    font-style:normal;
}

.home_banner_text h1
{
    font-size:19px;
    text-align:right;
    font-weight:normal;
    padding:0px 13px 0px 5px;
    color:#bbb;
    white-space:nowrap;
}
.heading_container #heading
{
    display:none;
}   

.content_container
{
    background:#fff url("<?php echo $graphicsDir; ?>/featured_bg.gif") repeat-x left 128px;
}

.home_section_left
{
    background:#fff url(<?php echo $graphicsDir ?>/featured_bg.gif) repeat-x left -10px;
}

.home_content
{
    padding:9px 5px 9px 16px;    
}

.home_about, .home_content
{
    background:transparent url(<?php echo $graphicsDir ?>/circle_shadow.png) repeat-x left -19px;
}
.home_about
{
    background-position:60px -19px;
}

.home_content a
{
    color:#555;
    margin:5px 0px;
}

.icon_link
{
    background:url(<?php echo $graphicsDir; ?>/homeicons.gif) no-repeat left top;
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

.home_heading
{
    height:42px;
    background:url(<?php echo $graphicsDir; ?>/home_headings.gif?v2) repeat-x left top;
    border: 1px solid black;
}
.home_heading h4
{
    font-weight:bold;
    text-align:center;
    padding-top:10px;
    font-size:14px;
}

.heading_blue
{
    background-position:left top;
    border-color:#bccdd5;
}

.heading_green
{
    background-position:left -42px;
    border-color:#bbc388;
}

.heading_gray
{
    background-position:left -84px;
    border-color:#e3dfd6;
}

.home_section_left
{
    border-right:3px solid #fff;
}

.home_about
{   
    padding:15px 10px 10px 10px;    
    font-size:12px;
}

.home_about .submit_button
{
    margin:0px; padding:0px;
}

.home_bottom_left
{
    background:url(<?php echo $graphicsDir; ?>/anothershadow.gif) no-repeat 3px top;
    padding-top:16px;
}

.home_more
{
    font-style:italic;
    font-size:11px;
}

.home_featured
{    
    margin-left:1px;
    width:445px;
    padding:2px;
    background:#fff url(<?php echo $graphicsDir; ?>/featured_bg.gif) repeat-x left top;
    border:1px solid #e2dfd6;
    margin-right:3px; 
}

.home_featured_heading
{
    border-bottom:1px solid #c5c4c0;
    padding:10px 15px;
    font-size:14px;
    font-weight:normal;
}

.home_featured_content
{
    border-top:1px solid #fff;
    padding:12px 15px;
}

.home_bottom_right
{
    border-left:1px solid #e3dfd6;
    border-right:1px solid #e3dfd6;
    background:#fff url(<?php echo $graphicsDir; ?>/what_bg.gif) repeat-x left bottom;
}
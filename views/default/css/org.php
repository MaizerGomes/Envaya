<?php
    include(__DIR__."/default.php");
    $graphicsDir = "/_graphics/green";    
?>

.content_container .thin_column
{
    padding-bottom:1px;
    background-color:#e5e5e5;
}

.section_header
{
    font-family:Verdana, sans-serif;
    height:21px;
}

.section_content
{
    padding-top:12px;
    padding-bottom:12px;
}

#no_site_menu
{
    height:8px;
}

#site_menu
{
    padding-left:12px;
}

#site_menu a
{
    color:#686464;
    display:block;
    float:left;
    line-height: 34px;
    height:34px;
    padding-left:3px;
    margin:0px 1px 8px 0px;
    text-decoration:none;
}

#site_menu a.selected,
#site_menu a:hover
{
    color:black;
    background-color:#d5d0c8;
}

#site_menu a span
{
    padding:0 6px 0 3px;
    height:34px;
    cursor:pointer;
}

#site_menu a.selected span,
#site_menu a:hover span
{
    display:block;
}

.language
{
    padding-top:5px;
}
<?php
    $contentWidth = 500;
    include(__DIR__."/default.php");

    $graphicsDir = $vars['url'] . "_graphics";
?>

body
{
    background-color:#e7e2d7;
}

#content_top
{
    height:24px;
    background:#fff url("<?php echo $graphicsDir; ?>/contenttop.gif") no-repeat left top;
}

#content_bottom
{
    height:24px;
    background:#fff url("<?php echo $graphicsDir; ?>/contentbottom.gif") no-repeat left top;
}

#content_mid
{
    background:#fff url("<?php echo $graphicsDir; ?>/contentgradient.gif") repeat-y left top;
    padding:0px 6px;
}

.section_header
{
    background:#e6e6e6 url("<?php echo $graphicsDir; ?>/sectionheader.gif") no-repeat left top;
    height:21px;
    font-family:Verdana, sans-serif;
}

.thin_column
{
    width:<?php echo $contentWidth - 7 ?>px;
}

#translate_bar
{
    margin-bottom:0px;
}
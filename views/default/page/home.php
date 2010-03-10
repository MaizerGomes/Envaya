<?php
    global $CONFIG;
    function language_link($lang)
    {
        if ($lang == get_language())
        {
            return "<b>" . elgg_echo($lang, $lang) . "</b>";
        }
        else 
        {
            echo "<a href='{$CONFIG->wwwroot}action/changeLanguage?newLang=$lang'>".elgg_echo($lang, $lang)."</a>";
        }
    }
    ?>

<div class='homeLanguages'>
    <?php echo language_link('en'); ?>
    &middot;
    <?php echo language_link('sw'); ?>   
</div>

<div class='homeHeading'><?php echo elgg_echo("home:heading") ?></div>

<table width='100%'>
<tr>
<td width='50%'>
<div class='homeSubheading'><?php echo elgg_echo("home:for_organizations") ?></a></div>

<div class='homeSection'>
    <a class='homeSectionIcon' href='pg/org/new'><img src='_graphics/icons/default/small.png' /></a>
    <a href='pg/org/new'><?php echo elgg_echo("home:sign_up") ?></a>    
</div>   

<div class='homeSection'>
    <a class='homeSectionIcon' href='pg/page/why'><img src='_graphics/icons/default/small.png' /></a>
    <a href='pg/page/why'><?php echo elgg_echo("home:why") ?></a>    
</div>   

<div class='homeSection'>
    <a class='homeSectionIcon' href='pg/login'><img src='_graphics/icons/default/small.png' /></a>
    <a href='pg/login'><?php echo elgg_echo("home:sign_in") ?></a>    
</div>   

</td>
<td  width='50%'>
<div class='homeSubheading'><?php echo elgg_echo("home:for_everyone") ?></div>

<div class='homeSection'>
    <a class='homeSectionIcon' href='pg/org/browse'><img src='_graphics/globe.gif' /></a>
    <a href='pg/org/browse'><?php echo elgg_echo("home:browse_orgs") ?></a>
</div>

<div class='homeSection'>
    <a class='homeSectionIcon' href='pg/org/search'><img src='_graphics/search.gif' /></a>
    <a href='pg/org/search'><?php echo elgg_echo("home:find_org") ?></a>
</div>

<div class='homeSection'>
    <a class='homeSectionIcon' href='pg/page/about'><img src='_graphics/icons/default/small.png' /></a>
    <a href='pg/about'><?php echo elgg_echo("home:about_us") ?></a>
</div>   

</table>   
    <?php
    
    

    $area = "<div><a href='{$CONFIG->wwwroot}pg/org/new'>".elgg_echo("register_org")."</a></div>";
    $area .= "<div><a href='{$CONFIG->wwwroot}pg/org/browse'>".elgg_echo("org:list_all")."</a></div>";
    
    $area .= "<form method='GET' action='".$CONFIG->wwwroot."pg/org/search/'><input type='text' name='q'><input type='submit' value='".elgg_echo('search')."'></form>";
    
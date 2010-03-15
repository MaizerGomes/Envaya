<div id='languages' class='dropdown' style='display:none'>
    <div class='dropdown_title'><?php echo elgg_echo("language:choose"); ?></div>
    <div class='dropdown_content'>
        <?php 
            $translations = get_installed_translations();
            $curLang = get_language();
            foreach ($translations as $lang => $name)
            {
                $class = ($curLang == $lang) ? " dropdown_item_selected" : "";
                echo "<a class='dropdown_item{$class}' href='action/changeLanguage?newLang={$lang}'>$name</a>";
            }
        ?>    
    </div>
</div>
<script type='text/javascript'>
function openChangeLanguage()
{
    var languages = document.getElementById('languages');
    
    if (languages.style.display == 'none')
    {
        var languageButton = document.getElementById('languageButton');
        languages.style.left = languageButton.offsetLeft + "px";
        languages.style.top = (languageButton.offsetTop + 50) + "px";
        languages.style.display = 'block';

        setTimeout(function() {
            addEvent(document.body, 'click', closeChangeLanguage);
        }, 1);    
    }    
}
function closeChangeLanguage()
{
    setTimeout(function() {
        var languages = document.getElementById('languages');
        languages.style.display = 'none';    
        removeEvent(document.body, 'click', closeChangeLanguage);
    }, 1);    
}
</script>
<div id="topbar">
<table id='topbarTable'>
<tr>
<td class='topbarLinks'>
    <a id='logoContainer' href="<?php echo ((isloggedin()) ? 'pg/dashboard' : 'pg/home') ?>">
        <img src="_graphics/logo.gif" alt="Envaya" width="145" height="30">
    </a>
          
    
<?php

    echo "<a href='org/browse'>".elgg_echo('browse')."</a>";
    echo "<a href='org/search'>".elgg_echo('search')."</a>";
    echo "<a href='javascript:void(0)' id='languageButton' onclick='openChangeLanguage()'>".elgg_echo('language')."</a>";
    echo get_submenu_group('b', 'canvas_header/topbar_submenu', 'canvas_header/topbar_submenu_group'); 
?>

<?php if (get_context() != "login") { ?>
<td width='238'>

    <?php            
    
        if (isloggedin())
        {
            
            echo "<div id='loggedinArea'><span class='loggedInAreaContent'>";
            
            if (get_loggedin_user()->isSetupComplete())
            {
                $url = get_loggedin_user()->getURL();
                echo "<a href='$url'><img src='_graphics/self.gif' height='25' width='26' /></a>";
                
                echo "<a href='pg/settings/' id='usersettings'><img src='_graphics/settings.gif' height='25' width='25' /></a>";                
            }
            

            // The administration link is for admin or site admin users only
            if ($vars['user']->admin) 
            {
                echo "<a href='pg/admin/'><img src='_graphics/admin.gif' height='25' width='24' /></a>";                
            }                    
            
            echo "<a href='action/logout'><img src='_graphics/logout.gif' height='25' width='22' /></a>";
            
            echo "</div></div>";
        }
        else
        {
            echo "<a id='loginButton' href='pg/login'><span class='loginContent'><img src='_graphics/lock.gif' height='20' width='20' /><span>".elgg_echo("login")."</span></span></a>";
        }        
    ?>    
    
</td>
<?php } ?>
</tr>
</table>

</div>

<div class="clearfloat"></div>
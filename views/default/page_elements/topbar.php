<div id="topbar">
<table class='topbarTable'>
<tr>
<td class='topbarLinks'>
    <a id='logoContainer' href="/home">
        <img src="/_graphics/logo_sm.gif?v5" alt="Envaya" width="120" height="25">
    </a>
    <a href='/envaya'><?php echo __('about') ?></a>
    <a href='/org/browse'><?php echo __('browse') ?></a>
    <a href='/org/search'><?php echo __('search') ?></a>
    <a href='/org/feed'><?php echo __('feed') ?></a>    
    <div class='top_language'>
    <?php
        echo __('language');
        echo '&nbsp;';
        
        echo view('input/pulldown', array(
            'name' => 'top_language',
            'id' => 'top_language',
            'options' => Language::get_options(),
            'value' => get_language(),
            'js' => "onchange='languageChanged()' onkeypress='languageChanged()'"
        ));
    ?>
    </div>
</td>
<td width='159'>&nbsp;</td>
</tr>
</table>

<?php if (!@$vars['hideLogin']) { ?>
<div id='topRight'><?php echo view('page_elements/login_area', $vars); ?></div>
<?php } ?>

</div>
<div style="clear:both"></div>
<?php 
    ob_start();
?>
<div id='content_wrapper'>
<table class='left_sidebar_table'>
    <tr>
    <td id='left_sidebar'>
    <?php            
        echo get_submenu_group('topnav', 'canvas_header/link_submenu', 'canvas_header/basic_submenu_group'); 
    ?>    
    </td>
    <td id='right_content'>
        <?php echo view('translation/control_bar'); ?>
        <?php echo $vars['area2']; ?>            
        <div style='clear:both'></div>        
    </td>
    </tr>
</table>
</div>
<div id='content_bottom'>
</div>

<?php 
    $content = ob_get_clean();
    echo view_layout("content_shell", $vars['area1'], $content, @$vars['area3']);    
?>

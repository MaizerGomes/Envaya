<div id="thin_column">
    <div id='heading'>
      <?php echo $vars['area1'] ?>  
    </div>

    <div id='content'>
        <?php            
            $submenu = get_submenu(); 
            if (!empty($submenu))
            {
                echo "<div id='sidebar_container'>$submenu</div>";
            }    
        ?>    
        </div>
        <div id='content_top'></div>
        <div id='content_mid'>       
            <?php echo $vars['area2']; ?>
            &nbsp;    
            <div style='clear:both'></div>
        </div>
        <div id='content_bottom'></div>        
    </div>
</div>


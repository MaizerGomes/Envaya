<?php
    $current = $vars['current'];
    
    $deletename = $vars['deletename'];
    $deleteid = $vars['deleteid'];
    $removable = isset($vars['removable']) ? $vars['removable'] : ($current != null);
    
    /*
    $imageInput = elgg_view("input/file", array(
        'internalname' => $vars['internalname'],
        'internalid' => $vars['internalid'],
        'js' => $vars['js']
    )); 
    */
    
    $imageInput = elgg_view('input/swfupload_image', array(
        'internalname' => $vars['internalname'],
        'trackDirty' => @$vars['trackDirty'],
        'thumbnail_size' => $vars['thumbnail_size'],
        'sizes' => $vars['sizes']
    ));      
?>

<?php if ($current) { ?>
    <table>
    <tr>
        <td style='padding-right:10px;width:100px'><?php echo __('image:current') ?><br />
            <img src='<?php echo $current ?>' />
        </td>
        <td> 
            <div>
                <?php echo __('image:new') ?>
                <?php echo $imageInput ?>                        
            </div>                                    
        <?php if ($removable) { ?>
            <div style='padding-top:10px'>
            <?php echo elgg_view('input/checkboxes', 
            array('internalname' => $deletename,
                'internalid' => $deleteid,
                'options' => array(__('image:delete')),
                'js' => $vars['js']
            )) ?>
            </div>
        <?php } ?>    
        </td>
    </tr>
    </table>    

<?php } else { ?>        
    <?php echo $imageInput ?>    
    <div class='help'><?php echo __('image:optional') ?></div>
<?php } ?>

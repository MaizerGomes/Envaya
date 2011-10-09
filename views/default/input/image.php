<?php
    $current = $vars['current'];
    
    $deletename = $vars['deletename'];
    $deleteid = @$vars['deleteid'];
    $removable = isset($vars['removable']) ? $vars['removable'] : ($current != null);
    
    $imageInput = view('input/image_uploader', array(
        'name' => $vars['name'],
        'track_dirty' => @$vars['track_dirty'],
        'thumbnail_size' => $vars['thumbnail_size'],
        'sizes' => $vars['sizes']
    ));      
?>

<?php if ($current) { ?>
    <table>
    <tr>
        <td style='padding-right:10px;width:100px'><?php echo __('upload:image:current') ?><br />
            <img src='<?php echo $current ?>' />
        </td>
        <td> 
            <div>
                <?php echo __('upload:image:new') ?>
                <?php echo $imageInput ?>                        
            </div>                                    
        <?php if ($removable) { ?>
            <div style='padding-top:10px'>
            <?php echo view('input/checkboxes', 
            array('name' => $deletename,
                'id' => $deleteid,
                'options' => array('1' => __('upload:image:delete')),
            )) ?>
            </div>
        <?php } ?>    
        </td>
    </tr>
    </table>    

<?php } else { ?>        
    <?php echo $imageInput ?>    
    <div class='help'><?php echo __('upload:image:optional') ?></div>
<?php } ?>

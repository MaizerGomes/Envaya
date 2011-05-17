<?php
    $user = $vars['entity'];
    ob_start();
?>
    <div class='section_content padded'>
    <table class='inputTable'>
    <tr>
        <th><?php echo __('username'); ?>:</th>
        <td>
            <?php echo $user->username ?> <a href='<?php echo $user->get_url() ?>/username'><?php echo __('user:username:change'); ?></a>
        </td>
    </tr>    
    <tr>
        <th><?php echo __('password'); ?>:</th>
        <td>
            <span style='font-family:serif'>********</span> <a href='<?php echo $user->get_url() ?>/password'><?php echo __('user:password:change'); ?></a>
        </td>
    </tr>          
    <tr>
    <th><?php echo __('user:name:label'); ?>:</th>
    <td>
        <?php
            echo view('input/text',array('name' => 'name', 'trackDirty' => true, 'value' => $user->name));
        ?>
    </td>
    </tr>    
    <tr>
        <th><?php echo __('email'); ?>:</th>
        <td>
        <?php
            echo view('input/email',array(
                'name' => 'email', 
                'value' => $user->email, 
                'trackDirty' => true,
            ));
        ?>
        </td>
    </tr>
    <tr>
        <th><?php echo __('phone_number'); ?>:</th>
        <td>
        <?php
            echo view('input/text',array(
                'name' => 'phone', 
                'value' => $user->phone_number, 
                'trackDirty' => true
            ));
        ?>
        </td>
    </tr>

    <?php if (false) { ?>
    
    <tr>
        <th style='vertical-align:top;padding-top:8px'><?php echo __('user:websites'); ?>:</th>
        <td>
        <?php		
            echo view("input/text", array(
                'name' => 'websites[]', 
				'value' => '', 
            ));           
         ?>
        <div class='help'><?php echo __('user:websites:help'); ?></div>         
         </td>
    </tr>
        
    <?php } ?>
    <tr>
        <th><?php echo __('user:language:label'); ?>:</th>
        <td style='padding-top:8px'>
        <?php
            $value = Config::get('language');
            if ($user->language)
                $value = $user->language;

            echo view("input/pulldown", array('name' => 'language', 'value' => $value, 'options' => Language::get_options()));

         ?>
        </td>
    </tr>

    <?php if ($user instanceof Organization) { ?>
    
    <tr>

        <th style='vertical-align:top;padding-top:8px'><?php echo __('user:notification:label'); ?>:</th>
        <td  style='padding-top:8px'>
        <?php		
            echo view("input/checkboxes", array('name' => 'notifications', 
				'value' => $user->get_notifications(), 
				'options' => Notification::get_options()
            ));           
         ?>
        </td>
    </tr>
    <?php 
    }
    ?>
    </table>
    <?php

    echo view('input/hidden', array('name' => 'from', 'value' => get_input('from')));
    
    if (Session::isadminloggedin())
    {   
        echo view('input/alt_submit', array(
            'name' => 'delete', 
            'id' => 'widget_delete', 
            'confirmMessage' => __('areyousure'),
            'value' => __('user:delete'))); 
    }
    
    echo view('input/submit', array('value' => __('savechanges'), 'trackDirty' => true)); 

?>
</div>

<?php 
    $form_body = ob_get_clean();
    
    echo view('input/form',
        array('enctype' => 'multipart/form-data', 'action' => secure_url("{$user->get_url()}/settings"),
        'body' => $form_body));
?>
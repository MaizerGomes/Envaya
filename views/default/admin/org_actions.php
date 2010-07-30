<div class='adminBox'>
        <?php

        $org = $vars['entity'];
        $widget = $vars['widget'];

        if ($org->approval == 0)
        {
            echo elgg_view('output/confirmlink', array(
                'text' => __('approval:approve'),
                'is_action' => true,
                'href' => "admin/approve?org_guid={$org->guid}&approval=2"
            ));
            echo elgg_view('output/confirmlink', array(
                'text' => __('approval:reject'),
                'is_action' => true,
                'href' => "admin/approve?org_guid={$org->guid}&approval=-1"
            ));
        }
        else
        {
            echo elgg_view('output/confirmlink', array(
                'text' => __($org->approval > 0 ? 'approval:unapprove' : 'approval:unreject'),
                'is_action' => true,
                'href' => "admin/approve?org_guid={$org->guid}&approval=0"
            ));
        }

        if ($org->approval < 0)
        {
            echo elgg_view('output/confirmlink', array(
                'text' => __('approval:delete'),
                'is_action' => true,
                'href' => "admin/delete_entity?guid={$org->guid}"
            ));
        }

        echo "<a href='admin/add_featured?username={$org->username}'>".__('featured:add')."</a>";
        echo "<a href='{$org->username}/dashboard'>".__('dashboard')."</a>";
        echo "<a href='{$org->username}/settings'>".__('help:settings')."</a>";
        echo "<a href='{$org->username}/username'>".__('username:title')."</a>";
        
        if ($widget)
        {
            echo "<a href='{$widget->getBaseURL()}/options'>".__('widget:options')."</a>";
        }

        ?>
</div>
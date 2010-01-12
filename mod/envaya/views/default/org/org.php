<?php
	/**
	 * Elgg groups plugin full profile view.
	 *
	 * @package ElggGroups
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @author Curverider
	 * @copyright Curverider Ltd 2008-2009
	 * @link http://elgg.com/
	 */

	if ($vars['full'] == true) {
		$iconsize = "large";
	} else {
		$iconsize = "medium";
	}

?>

<div id="groups_info_column_right"><!-- start of groups_info_column_right -->
    <div id="groups_icon_wrapper"><!-- start of groups_icon_wrapper -->

        <?php
		    echo elgg_view(
					"org/icon", array(
												'entity' => $vars['entity'],
												//'align' => "left",
												'size' => $iconsize,
											  )
					);
        ?>

    </div><!-- end of groups_icon_wrapper -->
</div><!-- end of groups_info_column_right -->

<div id="groups_info_column_left"><!-- start of groups_info_column_left -->
    <?php
        if ($vars['full'] == true) {

		        foreach($vars['config']->org_fields as $shortname => $valtype) {
			        if ($shortname != "name") {
				        $value = $vars['entity']->$shortname;

					    if (!empty($value)) {
					        //This function controls the alternating class
                		    $even_odd = ( 'odd' != $even_odd ) ? 'odd' : 'even';
					    }

					    echo "<p class=\"{$even_odd}\">";
						echo "<b>";
						echo elgg_echo("org:{$shortname}");
						echo ": </b>";

						echo elgg_view("output/{$valtype}",array('value' => $vars['entity']->$shortname));

						echo "</p>";
				    }
				}

                echo "<p><b>";
                echo elgg_echo("org:map");
                echo ": </b>";
                $entityLat = $vars['entity']->getLatitude();
                if (!empty($entityLat)) 
                { 
                    echo elgg_view("org/map", array(
                        'lat' => $entityLat, 
                        'long' => $vars['entity']->getLongitude(),
                        'zoom' => 10,
                        'width' => 300,
                        'pin' => true,
                        'height' => 200
                    ));                                  
                }
                echo "</p>";
		}
	?>
</div><!-- end of groups_info_column_left -->

<div id="groups_info_wide">

	<p class="groups_info_edit_buttons">

<?php
	if ($vars['entity']->canEdit())
	{

?>

		<a href="<?php echo $vars['url']; ?>mod/envaya/editOrg.php?group_guid=<?php echo $vars['entity']->getGUID(); ?>"><?php echo elgg_echo("edit"); ?></a>


<?php

	}

?>

	</p>
</div>
<div class="clearfloat"></div>
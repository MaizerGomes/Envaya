
<?php

    $editPinMode = $vars['edit'];
    $zoom = $vars['zoom'] ? $vars['zoom'] : 10;
    $width = $vars['width'] ? $vars['width'] : 460;
    $height = $vars['height'] ? $vars['height'] : 280;
    $apiKey = get_plugin_setting('google_api', 'googlegeocoder');
    $lat = $vars['lat'];
    $long = $vars['long'];

    if (!$vars['static'])
    {
        if ($editPinMode && !$vars['pin'])
        {
?>          
            <div id="dropPinBtn">
            <a href="javascript:dropPin();"><?php echo elgg_echo("org:mapDropPin"); ?></a>
            </div>
<?php
        }
?>

<div id="pinDragInstr" style="display:none;">
<?php echo elgg_echo("org:mapPinDragInstr"); ?>
</div>

<div id='map' style='width:<?php echo $width; ?>px;height:<?php echo $height; ?>px'></div>
<script type="text/javascript" src="http://www.google.com/jsapi?key=<?php echo $apiKey ?>"></script>
<script type="text/javascript">
  google.load("maps", "2.x");

  function bind(obj, fn)
  {
    return function() {
        return fn(obj);
    };
  }

  function dropPin()
  {
    var $ll = map.getCenter();
    if($ll)
    {
        placeMarker($ll);
        document.getElementById("dropPinBtn").style.display = "none";
    }
  }
  
  function setSavedLL($ll)
  {
      document.getElementById("orgLat").value = $ll.lat();
      document.getElementById("orgLng").value = $ll.lng();
  }
  
  function placeMarker($ll)
  {
      <?php
      if ($editPinMode) {
      ?>
          var marker = new GMarker($ll, {draggable: true});

          GEvent.addListener(marker, "dragend", function(latlng) {
            setSavedLL(latlng);
            map.setCenter(latlng);
            });
      
          map.addOverlay(marker);
          setSavedLL($ll);
          document.getElementById("pinDragInstr").style.display = "block";
          document.getElementById("saveMapForm").style.display = "block";
      <?php
      }
      else {
      ?>
        map.addOverlay(new GMarker($ll));
      <?php
      }
      ?>
  }
  
  // Call this function when the page has been loaded
  function initialize() {
    map = new google.maps.Map2(document.getElementById("map"));
    map.addControl(new GSmallMapControl());
    map.addControl(new GMapTypeControl());

    var center = new google.maps.LatLng(<?php echo $lat; ?>,<?php echo $long; ?>);

    map.setCenter(center, <?php echo $zoom; ?>);
        
    <?php
        if ($vars['pin']) {
    ?>
            placeMarker(center);
    <?php
        }
    ?>

    <?php
        if ($vars['nearby']) {
            foreach($vars['nearby'] as $org) {
    ?>
                    var icon = new GIcon(G_DEFAULT_ICON);
                    icon.image = "<?php echo $org->getIcon('tiny'); ?>"
                    icon.iconSize = new GSize(20,20);
                    icon.iconAnchor = new GPoint(10, 10);
                    var markerOptions = { icon:icon };

                var point = new GLatLng(<?php echo $org->getLatitude(); ?>, <?php echo $org->getLongitude(); ?>);
                var marker = new GMarker(point, markerOptions);

                GEvent.addListener(marker, 'click', bind(marker,
                    function (marker){
                        marker.openInfoWindowHtml([
                            '<h3><a href="<?php echo $org->getUrl(); ?>">',
                            <?php echo json_encode(escape($org->name)); ?>,
                            '</a></h3><p>',
                            <?php
                                $description = $org->description;

                                if ($description && strlen($description) > 300)
                                {
                                    $description = substr($description, 0, 300) ."...";
                                }
                                echo json_encode(escape($description));

                            ?>,
                            '</p>'
                        ].join(""), {maxWidth:350});
                    }
                ));

                map.addOverlay(marker);
    <?php
            }
        }
    ?>

  }
  google.setOnLoadCallback(initialize);
</script>

<?php 
    if ($editPinMode) {
?>
        <form id="saveMapForm" style="display:none;" action="<?php echo $vars['url']; ?>action/org/editMap" enctype="multipart/form-data" method="post">
            <?php echo elgg_view('input/securitytoken'); ?>
            <input type="hidden" name="org_guid" value="<?php echo $vars['org']->guid; ?>" />
            <input type="hidden" id="orgLat" name="org_lat" value="" />
            <input type="hidden" id="orgLng" name="org_lng" value="" />
            <input type="submit" class="submit_button" value="<?php echo elgg_echo("org:saveMapEdit"); ?>" />
        </form>

<?php
}
?>

<?php
    } // not static
    else
    {
        echo "<div>";
		echo "<a href='".$vars['config']->url."pg/org/search/?lat=$lat&long=$long'>";
        echo "<img width='$width' height='$height' src='http://maps.google.com/maps/api/staticmap?center=$lat,$long&zoom=$zoom&size={$width}x$height&maptype=roadmap&markers=$lat,$long&sensor=false&key=$apiKey' />";
		echo "</a>";
        echo "</div>";
    }

?>
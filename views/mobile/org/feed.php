<?php

$sector = $vars['sector'];
$region = $vars['region'];
$country = $vars['country'];
$items = array_slice($vars['items'], 0, 10);

?>
<?php

echo view('org/current_filter', array(
    'sector' => $sector, 
    'region' => $region, 
    'country' => $country,
    'changeurl' => '/pg/change_feed_view'));
echo view('feed/list', array('items' => $items));

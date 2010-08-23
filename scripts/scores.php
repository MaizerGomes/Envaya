<?php

require_once("scripts/cmdline.php");
require_once("engine/start.php");


$scores = array();
foreach (Organization::query()->filter() as $org)
{
    $scores[$org->get_website_score()][] = $org;
}

for ($i = 0; $i < 100; $i++)
{
    $orgs = @$scores[$i];
    if ($orgs)
    {
        foreach ($orgs as $org)
        {
            echo "$i $org->username $org->name\n";
        }
    }
}

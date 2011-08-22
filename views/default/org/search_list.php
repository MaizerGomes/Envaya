<?php    
    $sector = null;
    $region = null;
    $fulltext = null;
    $country = null;
    $limit = 10;
    extract($vars);
    
    $offset = (int) get_input('offset');

    $query = Organization::query()->where_visible_to_user();
    $query->with_country($country);
    $query->with_sector($sector);
    $query->with_region($region);
     
    if ($fulltext)
    {
        $query->fulltext($fulltext);
    }
    else
    {
        $query->order_by('name');                
    }
                
    $query->limit($limit, $offset);
       
    echo view('search/results_list', array(
        'entities' => $query->filter(),
        'count' => $query->count(),
        'offset' => $offset,
        'limit' => $limit,
    ));

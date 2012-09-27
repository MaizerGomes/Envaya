<script type='text/javascript'>

function selectOrg(guid, selected)
{
    var link = $('org_'+guid);
    link.style.color = selected ? '#333' : '';
}

function selectIfRecipient(guid, email)
{
    selectOrg(guid, parent.isRecipient(email));
}

function toggleRecipient(guid, email)
{   
    if (!parent.isRecipient(email))
    {
        selectOrg(guid, true);
        parent.addRecipient(email);
    }
    else
    {
        selectOrg(guid, false);
        parent.removeRecipient(email);
    }
}
</script>

<div class='padded'>
<?php
    echo "<div style='padding-bottom:5px'>";
    echo __('share:browse_instructions');
    echo "</div>";
    
    $filters = Query_Filter::filters_from_input(array(
        'Query_Filter_User_Sector',
        'Query_Filter_User_Country',
        'Query_Filter_User_Region'
    ));
       
    echo view('org/filter_controls', array(
        'baseurl' => '/pg/browse_email',
        'filters' => $filters        
    ));

    $query = Organization::query()
        ->where_visible_to_user()
        ->where("email <> ''")
        ->apply_filters($filters)
        ->order_by('name');    
    
    $limit = 10;
    $offset = Input::get_int('offset');
        
    $orgs = $query->limit($limit, $offset)->filter();
    $count = $query->count();
    
    if ($count)
    {
        echo view('pagination', array(
            'offset' => $offset,
            'limit' => $limit,
            'count' => $count,
        ));    
    
        echo "<ul>";
        foreach ($orgs as $org)
        {
            echo "<li>";
            echo "<a id='org_{$org->guid}' title='".escape($org->email)."' href='javascript:void(0)' 
                onclick='toggleRecipient(".json_encode($org->guid).",".json_encode($org->email).");'>";
            echo "<span style='font-weight:bold'>".escape($org->name)."</span>";
            echo "</a>";
            echo "<script type='text/javascript'>";
            echo "selectIfRecipient($org->guid,".json_encode($org->email).");";            
            echo "</script>";
            echo "</li>";
        }
        echo "</ul>";    
    }
    else
    {
        echo __("search:noresults");
    }

?>
</div>
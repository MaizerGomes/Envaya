<div class='padded'>
<?php   
    $subscription = $vars['subscription'];
    
    echo "<p>".sprintf(__('contact:select_email'), 
        "<a href='mailto:".escape($subscription->email)."'>".escape($subscription->email)."</a>")."</p>";

    $query = EmailTemplate::query()->order_by('time_created desc');
    $limit = 10;
    $offset = (int)get_input('offset');
    
    $emails = $query->limit($limit, $offset)->filter();
    $count = $query->count();
    
    $elements = array();
    
    $escFrom = urlencode(get_input('from'));
    
    foreach ($emails as $email)
    {
        ob_start();
        
        echo "<div style='padding:3px'>";
        
        $can_send = $email->can_send_to($subscription);
        
        $escSubject = escape($email->subject ?: '(No Subject)');
        
        if ($can_send)
        {
            echo "<a href='{$email->get_url()}/send?subscriptions[]={$subscription->guid}&from=$escFrom'><strong>$escSubject</strong></a>";
            
            echo " (".get_date_text($email->time_created).")";            
        }
        else
        {
            echo "<span style='color:#999'>$escSubject</span>";
            
            $outgoing_mail = $email->query_outgoing_messages_for_subscription($subscription)->get();
            if ($outgoing_mail)
            {            
                echo " ({$outgoing_mail->get_status_text()} ".get_date_text($outgoing_mail->time_created).") ";
                
                echo view('input/post_link', array(
                    'href' => "{$email->get_url()}/reset_outgoing?id={$outgoing_mail->id}",
                    'text' => 'reset'
                ));
            }
        }
                
        echo "</div>";
        
        $elements[] = ob_get_clean();
    }
   
    echo view('paged_list', array(
        'elements' => $elements,
        'count' => $count,
        'offset' => $offset,
        'limit' => $limit,
        'separator' => "<div class='separator'></div>"
    ));
?>
</div>
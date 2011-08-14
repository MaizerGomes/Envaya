<div class='padded'>
<?php
    $mail = $vars['mail'];
    
    echo "<table class='gridTable'>";
    
    echo "<tr>";
    echo "<th>Time Queued</th>";
    echo "<td style='white-space:nowrap'>".friendly_time($mail->time_queued)."</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<th>Time Sent</th>";
    echo "<td style='white-space:nowrap'>".friendly_time($mail->time_sent)."</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<th>Status</th>"; 
    echo "<td>".$mail->get_status_text()."<td>";    
    echo "</tr>"; 

    foreach ($mail->getHeaders() as $name => $value_arr)
    {
        echo "<tr>";
        echo "<th>".escape($name)."</th>";
        echo "<td>";
        foreach ($value_arr as $key => $value)
        {            
            if ($key !== 'append')
            {
                echo "<div>".escape($value)."</div>";
            }
        }
        echo "</td>";
        echo "</tr>";
    }    
    
    $bodyText = $mail->getBodyText();  
    if ($bodyText)
    {
        echo "<tr>";
        echo "<th>Body (Text)</th>"; 
        echo "<td>".view('output/longtext', array('value' => $bodyText->getRawContent()))."</td>";    
        echo "</tr>"; 
    }       

    $bodyHtml = $mail->getBodyHtml();
    if ($bodyHtml)
    {
        echo "<tr>";
        echo "<th>Body (HTML)</th>"; 
        echo "<td>".Markup::sanitize_html($bodyHtml->getRawContent())."</td>";    
        echo "</tr>"; 
    }           
    
    echo "</table>";

    echo "<div style='font-weight:bold;font-size:14px;text-align:center'>";
    echo view('admin/mail_actions', array('mail' => $mail));    
    echo "</div>";
?>

</div>
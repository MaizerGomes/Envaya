<?php

    /**
     * Elgg pagination
     *
     * @package Elgg
     * @subpackage Core
     * @author Curverider Ltd
     * @link http://elgg.org/
     *
     */

    if (!isset($vars['offset'])) {
        $offset = 0;
    } else {
        $offset = $vars['offset'];
    }

    $pagesShown = @$vars['pagesShown'] ?: 12;
    $pagesEdge = ceil($pagesShown/3);
    $pagesCenter = ceil($pagesShown/6);

    if ((!isset($vars['limit'])) || (!$vars['limit'])) {
        $limit = 10;
    } else {
        $limit = (int)$vars['limit'];
    }
    if (!isset($vars['count'])) {
        $count = 0;
    } else {
        $count = $vars['count'];
    }
    if (!isset($vars['word'])) {
        $word = "offset";
    } else {
        $word = $vars['word'];
    }
    if (isset($vars['nonefound'])) {
        $nonefound = $vars['nonefound'];
    } else {
        $nonefound = true;
    }

    $totalpages = ceil($count / $limit);
    $currentpage = ceil($offset / $limit) + 1;

    $baseurl = preg_replace('/[\&\?]'.$word.'\=[0-9]*/',"",$vars['baseurl']);

    //only display if there is content to paginate through or if we already have an offset
    if (($count > $limit || $offset > 0) && get_context() != 'widget') {

?>

<div class="pagination">
<?php

    if ($offset > 0) {

        $prevoffset = $offset - $limit;
        if ($prevoffset < 0) $prevoffset = 0;

        $prevurl = $baseurl;
        if (substr_count($baseurl,'?')) {
            $prevurl .= "&{$word}=" . $prevoffset;
        } else {
            $prevurl .= "?{$word}=" . $prevoffset;
        }

        echo "<a href=\"{$prevurl}\" class=\"pagination_previous\">&laquo; ". __("previous") ."</a> ";

    }

    if ($offset > 0 || $offset < ($count - $limit)) {

        $currentpage = round($offset / $limit) + 1;
        $allpages = ceil($count / $limit);

        $i = 1;
        $pagesarray = array();
        while ($i <= $allpages && $i <= $pagesEdge) {
            $pagesarray[] = $i;
            $i++;
        }
        $i = $currentpage - $pagesCenter;
        while ($i <= $allpages && $i <= ($currentpage + $pagesCenter)) {
            if ($i > 0 && !in_array($i,$pagesarray))
                $pagesarray[] = $i;
            $i++;
        }
        $i = $allpages - ($pagesEdge - 1);
        while ($i <= $allpages) {
            if ($i > 0 && !in_array($i,$pagesarray))
                $pagesarray[] = $i;
            $i++;
        }

        sort($pagesarray);

        $prev = 0;
        foreach($pagesarray as $i) {

            if (($i - $prev) > 1) {

                echo "<span class=\"pagination_more\">...</span>";

            }

            $counturl = $baseurl;
            $curoffset = (($i - 1) * $limit);
            if (substr_count($baseurl,'?')) {
                $counturl .= "&{$word}=" . $curoffset;
            } else {
                $counturl .= "?{$word}=" . $curoffset;
            }
            if ($curoffset != $offset) {
                echo " <a href=\"{$counturl}\" class=\"pagination_number\">{$i}</a> ";
            } else {
                echo "<span class=\"pagination_currentpage\"> {$i} </span>";
            }
            $prev = $i;

        }

    }

    if ($offset < ($count - $limit)) {

        $nextoffset = $offset + $limit;
        if ($nextoffset >= $count) $nextoffset--;

        $nexturl = $baseurl;
        if (substr_count($baseurl,'?')) {
            $nexturl .= "&{$word}=" . $nextoffset;
        } else {
            $nexturl .= "?{$word}=" . $nextoffset;
        }

        echo " <a href=\"{$nexturl}\" class=\"pagination_next\">" . __("next") . " &raquo;</a>";

    }

?>
<div class="clearfloat"></div>
</div>
<?php
    } // end of pagination check if statement
?>
<?php

/*
 * Represents a status message imported from a Facebook page using ExternalFeed_Facebook.
 */
class Widget_FacebookPost extends Widget_FeedItem
{
    function get_feed_name()
    {
        return 'Facebook';
    }

    function get_title()
    {
        return 'Facebook Post';
    }    
    
    function get_title_view()
    {
        return 'empty';
    }    
}

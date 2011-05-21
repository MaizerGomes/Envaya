<?php

class ExternalFeed_Twitter extends ExternalFeed
{
    function get_widget_subclass()
    {
        return 'Tweet';
    }
    
    static function try_new_from_html($html, $url)
    {
        $dom = new DOMDocument;
        $status = @$dom->loadHTML($html);                
        if ($status)
        {
            foreach ($dom->getElementsByTagName('link') as $link)
            {
                $rel = $link->getAttribute('rel');
                $href = $link->getAttribute('href');
                if ($rel == 'alternate' && preg_match('/\.rss$/', $href))
                {
                    $feed = new ExternalFeed_Twitter();
                    $feed->url = $url;
                    $feed->feed_url = preg_replace('/\.rss$/', '.json', $href);
                    return $feed;
                }
            }
        }
        return null;
    }    
    
    protected function get_entry_external_id($tweet)
    {
        $id = @$tweet['id_str'];
        if (!$id)
        {
            throw new DataFormatException("Missing tweet ID");
        }
        return $id;
    }
    
    protected function get_entry_title($tweet)
    {
        return Markup::truncate_at_word_boundary($tweet['text'], 100);
    }
    
    protected function get_entry_time($tweet)
    {
        return strtotime($tweet['created_at']);
    }
    
    protected function get_entry_link($tweet)
    {
        $id = $tweet['id_str'];
        $text = $tweet['text'];
        $screen_name = $tweet['user']['screen_name'];
        return "http://twitter.com/{$screen_name}/status/{$id}";
    }
    
    protected function get_entry_content($tweet)
    {
        $content = escape($tweet['text']);
        $image = @$tweet['user']['profile_image_url'];
        $screen_name = @$tweet['user']['screen_name'];
        $name = @$tweet['user']['name'];
                
        // regex adapted from http://neverusethisfont.com/blog/2008/10/automatically-linking-twitter-usernames/
        $content = preg_replace('/(^|[^\w])@([\w]+)/', '$1<a href="http://twitter.com/$2">@$2</a>', $content);
        $content = preg_replace('/(^|[^\w\&])#([\w]+)/', '$1<a href="http://search.twitter.com/search?q=%23\2">#$2</a>', $content);            
        
        return view('widgets/tweet_content', array(
            'content' => $content,
            'profile_image_url' => $image,
            'screen_name' => $screen_name,
            'name' => $name,
        ));
    }
    
    protected function _update()
    {
        $response = $this->load_feed();
        
        $tweets = json_decode($response->content, true);
        if (!$tweets)
        {
            throw new DataFormatException("Can't load JSON");
        }        
        
        return $this->update_entries($tweets);        
    }                
}     
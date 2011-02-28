<?php

/*
 * Functions for processing HTML in user-generated content.
 */
class Markup
{
    const Normal = null;
    const Feed = 'feed';

    /**
     * Takes a string and turns any URLs into formatted links
     *
     * @param string $text The input string
     * @return string The output stirng with formatted links
     **/
    static function parse_urls($text) {

        return preg_replace_callback('/(?<!=["\'])((ht|f)tps?:\/\/[^\s\r\n\t<>"\'\!\(\)]+)/i',
        create_function(
            '$matches',
            '
                $url = $matches[1];
                $urltext = str_replace("/", "/<wbr />", $url);
                return "<a href=\"$url\" style=\"text-decoration:underline;\">$urltext</a>";
            '
        ), $text);
    }

    static function autop($p, $br = 1)
    {
        $p = str_replace(array("\r\n", "\r"), "\n", $p); // cross-platform newlines
        $p = preg_replace("/\n\n+/", "\n\n", $p); // take care of duplicates
        $p = trim($p);
        $p = preg_replace("/\n/", "<br />", $p);
        return $p;
    }

    static function get_snippet($content, $maxLength = 100)
    {
        if ($content)
        {
            $cacheKey = "snippet_".md5($content)."_$maxLength";
            $cache = get_cache();
            $snippet = $cache->get($cacheKey);
            if (!$snippet)
            {
                $content = preg_replace('/<img[^>]+>/i', '', $content);
                $content = preg_replace('/<\/(p|h1|h2|h3)>/i', '</$1> <br />', $content);

                $tooLong = strlen($content) > $maxLength;
                // todo: multi-byte support
                if ($tooLong)
                {
                    $shortStr = substr($content, 0, $maxLength);

                    $lastSpace = strrpos($shortStr, ' ');
                    if ($lastSpace && $lastSpace > $maxLength / 2)
                    {
                        $shortStr = substr($shortStr, 0, $lastSpace);
                    }

                    $content = $shortStr;
                }
                $content = Markup::sanitize_html($content, array('HTML.AllowedElements' => 'a,em,strong,br','AutoFormat.RemoveEmpty' => true));
                $content = mb_ereg_replace('(\xc2\xa0)+',' ',$content); # non-breaking space
                $content = preg_replace('/(<br \/>\s*)+/', ' &ndash; ', $content);
                $content = preg_replace('/&ndash;\s*$/', '', $content);
                $content = preg_replace('/^\s*&ndash;/', '', $content);
                $content = preg_replace('/(&nbsp;)+/', ' ', $content);

                if ($tooLong)
                {
                    $content = $content."...";
                }
                $snippet = $content;
                $cache->set($cacheKey, $snippet);                               
            }

            return $snippet;
        }
        return '';
    }
    
    private static function get_purifier_config($options = null)
    {
        require_once(dirname(__DIR__).'/vendors/htmlpurifier/library/HTMLPurifier.auto.php');
    
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', Config::get('dataroot'));
        $config->set('AutoFormat.Linkify', true);
        if ($options)
        {
            foreach ($options as $k => $v)
            {
                $config->set($k, $v);
            }
        }

        $config->set('HTML.DefinitionID', 'EnvayaHTMLExtensions');
        $config->set('HTML.DefinitionRev', 3);
        $config->set('Cache.DefinitionImpl', null); 

        $def = $config->getHTMLDefinition(true);

        /*
         * Would like to do something like envaya:scribd for custom tags, but HTMLPurifier uses
         * DOMDocument which strips out any namespaces we set (except xml).
         */
        $scribd = $def->addElement(
          'scribd',   
          'Inline',  
          'Empty', 
          'Common', 
          array( 
            'docid' => 'Number',
            'width' => 'Number',
            'height' => 'Number',
            'guid' => 'Number',
            'filename' => 'Text',
            'accesskey' => 'Text'
          )
        );
        
        return $config;
    }
    
    static function sanitize_html($html, $options = null)
    {              
        $html = static::parse_editor_html($html);                    
        $config = static::get_purifier_config($options);
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }    
    
    static $scribd_re = '/<scribd ([^>]*)\/>/';
    
    static function render_custom_tags($html, $mode = null)
    {        
        $scribd_fn = ($mode == static::Feed) ? 'render_scribd_link' : 'render_scribd_embed';
        return preg_replace_callback(static::$scribd_re, array('Markup', $scribd_fn), $html);
    }
        
    static function render_editor_html($html)
    {
        return preg_replace_callback(static::$scribd_re, array('Markup', 'render_scribd_placeholder'), $html);
    }
    
    static function parse_editor_html($html)
    {
        return preg_replace_callback('/<img ([^>]*)class=[\'"]scribd_placeholder[\'"]([^>]*)\/>/', 
            array('Markup', 'undo_scribd_placeholder'), $html);
    }
    
    private static function render_scribd_view($view_name, $match)
    {
        $scribd = new SimpleXMLElement($match[0]);
        if ($scribd)
        {
            return view($view_name, array(
                'docid' => @$scribd['docid'], 
                'accesskey' => @$scribd['accesskey'],
                'filename' => @$scribd['filename'],
                'guid' => @$scribd['guid']
            ));
        }
        return '';    
    }
    
    private static function render_scribd_embed($match)
    {        
        return static::render_scribd_view('output/scribd', $match);
    }    
    
    private static function render_scribd_placeholder($match)
    {
        return static::render_scribd_view('output/scribd_placeholder', $match);
    }    

    private static function render_scribd_link($match)
    {
        return static::render_scribd_view('output/scribd_link', $match);
    }    
    
    private static function undo_scribd_placeholder($match)
    {
        $img = new SimpleXMLElement($match[0]);
        if ($img && @$img['alt'])
        {
            $alt = $img['alt'];
            $metadata = explode(':', $alt);
            return "<scribd docid='".escape($metadata[1])
                ."' accesskey='".escape($metadata[2])
                ."' guid='".(int)($metadata[3])
                ."' filename='".escape($metadata[0])."' />";
        }        
        return '';    
    }
}
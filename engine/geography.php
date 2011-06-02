<?php

class Geography
{
    /**
     * Interface for Google's geocode API, associating free-text place names with latitude/longitude.
     * 
     * Encode a location into a latitude and longitude, caching the result.
     *
     * @param String $location The location, e.g. "London", or "24 Foobar Street, Gotham City"
     * @param String $region The region code, specified as a ccTLD ("top-level domain") two-character value.
     */
    static function geocode($location, $region = '')
    {
        $row = Database::get_row("SELECT lat, `long` from geocode_cache WHERE location = ? AND region = ?", 
            array($location, $region)
        );
        
        if ($row)
        {
            return array(
                'lat' => $row->lat, 
                'long' => $row->long
            );
        }

        $latlong = static::google_geocode($location, $region);

        // If returned, cache and return value
        if ($location)
        {
            $lat = $latlong['lat'];
            $long = $latlong['long'];

            // Put into cache at the end of the page since we don't really care that much
            Database::execute_delayed(
                "INSERT DELAYED INTO geocode_cache (location, lat, `long`, region) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE lat=?, `long`=?",
                array($location, $lat, $long, $region, $lat, $long)
            );
        }

        return $latlong;
    }
    
    static function google_geocode($location, $region = '')
    {        
        $address = "http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($location)
            ."&sensor=false";
            
        if ($region)
        {
            $address .= "&region=" . urlencode($region);
        }

        $response = file_get_contents($address);
        $obj = json_decode($response, true);

        if (@$obj['status'] == 'OK')
        {
            $latlong = $obj['results'][0]['geometry']['location'];
            
            if ($latlong)
            {
                return array(
                    'lat' => (float)$latlong['lat'], 
                    'long' => (float)$latlong['lng']
                );
            }   
        }
        return null;
    }
    
    static function get_static_map_url($vars)
    {
        $lat = null;
        $long = null;
        $zoom = 10;
        $width = 460;
        $height = 280;
        $pin = false;
        extract($vars);
        
        $api_key = Config::get('google_api_key');
        
        $url = "http://maps.google.com/maps/api/staticmap?center={$lat},{$long}&zoom={$zoom}"
            ."&size={$width}x{$height}&maptype=roadmap&sensor=false&key={$api_key}";
        
        if ($pin)
        {
            $url .= "&markers={$lat},{$long}";
        }
        
        return $url;
    }

    static function is_supported_country($country_code)
    {
        return in_array($country_code, static::get_supported_countries());
    }
    
    static function get_supported_countries()
    {
        return array('tz','lr','rw');
    }
    
    static function get_country_codes()
    {
        // lowercase version of http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2 code;
        // same as used by GeoIP class
        return array('tz','lr','rw','us');
    }
    
    static function get_country_options()
    {
        $options = array();
        foreach (static::get_country_codes as $country)
        {
            $options[$country] = __("country:$country");
        }
        asort($options);
        return $options;
    }
    
    static function get_region_codes($country_code)
    {
        if ($country_code == 'tz')
        {
            return array(
                'region:tz:arusha',
                'region:tz:dar',
                'region:tz:dodoma',
                'region:tz:iringa',
                'region:tz:kagera',
                'region:tz:kigoma',
                'region:tz:kilimanjaro',
                'region:tz:lindi',
                'region:tz:manyara',
                'region:tz:mara',
                'region:tz:mbeya',
                'region:tz:morogoro',
                'region:tz:mtwara',
                'region:tz:mwanza',
                'region:tz:pemba_n',
                'region:tz:pemba_s',
                'region:tz:pwani',
                'region:tz:rukwa',
                'region:tz:ruvuma',
                'region:tz:shinyanga',
                'region:tz:singida',
                'region:tz:tabora',
                'region:tz:tanga',
                'region:tz:zanzibar_cs',
                'region:tz:zanzibar_n',
                'region:tz:zanzibar_w',
            );
        }
        if ($country_code == 'lr')
        {
            return array(
                'region:lr:bomi',
                'region:lr:bong',
                'region:lr:gbarpolu',
                'region:lr:grandbassa',
                'region:lr:grandcapemount',
                'region:lr:grandgedeh',
                'region:lr:grandkru',
                'region:lr:lofa',
                'region:lr:margibi',
                'region:lr:maryland',
                'region:lr:montserrado',
                'region:lr:nimba',
                'region:lr:rivercess',
                'region:lr:rivergee',
                'region:lr:sinoe',
            );
        }
        else
        {
            return array();
        }    
    }
    
    /*
     * Returns an associative array of region codes => localized text labels
     * for a given country.
     */
    static function get_region_options($country_code)
    {
        $res = array();
        foreach (static::get_region_codes($country_code) as $region_code)
        {
            $res[$region_code] = __($region_code);
        }
        asort($res);
        return $res;
    }
}

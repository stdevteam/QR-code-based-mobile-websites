<?php

class GeocoderComponent extends Object 
{ 
    // URL Variable Seperator 
    var $uvs        = ', '; 

    // You Google Map API Key here
    var $apiKey        = ''; 

    var $controller    = true; 

    function startup(&$controller) 
    { 
        $this->controller = &$controller; 
    } 

    function getLatLng($addy, $api_key = null){ 

        if(is_array($addy)){ 
            // First of all make the address 
            if(!empty($addressArr['zip'])){ 
                $address    = $addy['street'].$this->uvs.$addy['loc'].$this->uvs.$addy['zip']; 
            } 
            else{ 
                $address    = $addy['street'].$this->uvs.$addy['loc']; 
            } 
        }else{ 
            $address    = $addy; 
        } 
        // Default Api Key registered for webmechano. It's highly recommended that you use the one for stylished
        if($api_key == null){ 
            $api_key        = $this->apiKey; 
        } 
        $url        = "http://maps.google.com/maps/geo?output=xml&key=$api_key&q="; 

        // Here make the result array to return 
        // If the address is correct, it will return 200 in the CODE field so $result['code'] should be equal to 200
        $result        = array('lat'=>'', 'lng'=>'', 'code'=>''); 

        // Make the Temporary URL for CURL to execute 
        $tempURL    = $url.urlencode($address); 

        // Create the cURL Object here 
        $crl    = curl_init(); 
        curl_setopt($crl, CURLOPT_HEADER, 0); 
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1); 

        // Here we ask google to give us the lats n longs in XML format 
        curl_setopt($crl, CURLOPT_URL, $tempURL); 
        $gXML        = curl_exec($crl);    // Here we get the google result in XML 

        // Using SimpleXML (Built-in XML parser in PHP5) to parse google result 
        $goo        = simplexml_load_string(utf8_encode($gXML)); // VERY IMPORTANT ! - ACHTUNG ! - this line is for documents that are UTF-8 encoded 
        // If the layout and views are not UTF-8 encoded you can use the line below - 
        // comment the above line and un-comment the line below 
        // $goo        = simplexml_load_string($gXML); 

        $result['code']    = $goo->Response->Status->code; 
        if($result['code'] != 200){ 
            $result['lat']        = 'error'; 
            $result['lng']        = 'error'; 
            $result['address']    ='error'; 
            return $result; 
        } 
        else{ 
            $coords                = $goo->Response->Placemark->Point->coordinates; 
            list($lng, $lat)    = split(',', $coords); 
            $result['lat']        = $lat; 
            $result['lng']        = $lng; 
            $result['address']    = $goo->Response->Placemark->address; 
            return $result; 
        } 
    }// end function / action : getLatLng 

    function ip_lookup($ip_address) {
        $private_ips = array(
                '10.0.0.0|10.255.255.255',
                '172.16.0.0|172.31.255.255',
                '192.168.0.0|192.168.255.255',
                '169.254.0.0|169.254.255.255',
                '127.0.0.0|127.255.255.255',
                );
        $long_ip = ip2long($ip_address);
        if (-1 != $long_ip) {
            foreach ($private_ips as $private) {
                list ($start, $end) = explode('|', $private);
                if ($long_ip >= ip2long($start) && $long_ip <= ip2long($end)) {
                    return array(
                            'geoplugin_city' => '',
                            'geoplugin_region' => '',
                            'geoplugin_areaCode' => '',
                            'geoplugin_dmaCode' => '',
                            'geoplugin_countryCode' => '',
                            'geoplugin_countryName' => '',
                            'geoplugin_continentCode' => '',
                            'geoplugin_latitude' => '',
                            'geoplugin_longitude' => '',
                            'geoplugin_regionCode' => '',
                            'geoplugin_regionName' => '',
                            'geoplugin_currencyCode' => '',
                            'geoplugin_currencySymbol' => '',
                            'geoplugin_currencyConverter' => '',
                            );
                }
            }
        }
        $geo = file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip_address);
        return unserialize($geo);
    }
} 

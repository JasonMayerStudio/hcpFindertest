<?php
/**
 * Created by PhpStorm.
 * User: jtsuyuki
 * Date: 7/10/15
 * Time: 1:32 PM
 */

namespace App;

use Log;

/**
 * Class Geocode
 * @package App
 */
class Geocode
{
    /**
     * @var string
     */
    static  $google_api_key = 'AIzaSyBEuTJCekXGALEnsZqoQsO9e1m7Cy2YibE';
    //static  $google_api_key = 'AIzaSyBBg1cKVAO7Ksnzhs67P6r1W7EYrt7WRyE';


    /**
     * @param $address
     * @return mixed
     */
    public static function geocode($address)
    {
        $address = urlencode($address);
        $url = "https://maps.google.com/maps/api/geocode/json?sensor=false&address={$address}&key=".self::$google_api_key;
        // get the json response
        $resp_json = file_get_contents($url);
        Log::debug('json response \n' . $resp_json);

        // decode the json
        $resp = json_decode($resp_json, true);
        Log::debug('decoded json response \n', ['response'=>$resp] );

        return  $resp;
    }

    public static function getPartialMatchFromResponse($resp) {
        $partial_match = isset($resp['results'][0]['partial_match']) && $resp['results'][0]['partial_match']=="true";
        return $partial_match;
    }
}
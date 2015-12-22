<?php
/**
 * Created by PhpStorm.
 * User: jtsuyuki
 * Date: 7/10/15
 * Time: 1:43 PM
 */

namespace App;

use Log;
use DB;

class GeoCache
{
    var $id;
    var $address;
    var $geocoded_address;
    var $lat;
    var $lng;
    var $formatted_address;
    var $location_type;
    var $partial_match;

    private static $table = "geo_cache";

    /**
     * @return string
     */
    public static function getTable()
    {
        return self::$table;
    }


    /**
     * @param array $values_array
     */
    public function __construct(Array $values_array)
    {
        if(array_key_exists('address',$values_array)) $this->address = $values_array['address'];
        if(array_key_exists('geocoded_address',$values_array)) $this->geocoded_address = $values_array['geocoded_address'];
        if(array_key_exists('lat',$values_array)) $this->lat = $values_array['lat'];
        if(array_key_exists('lng',$values_array)) $this->lng = $values_array['lng'];
        if(array_key_exists('formatted_address',$values_array)) $this->formatted_address = $values_array['formatted_address'];
        if(array_key_exists('location_type',$values_array)) $this->location_type = $values_array['location_type'];
        if(array_key_exists('partial_match',$values_array)) $this->partial_match = $values_array['partial_match'];
    }

    /**
     * @param GeoCache $geo
     * @return int
     */
    public static function create(GeoCache $geo) {

        $id = DB::table(GeoCache::$table)->insertGetId(
            [
                'address' => $geo->address,
                'geocoded_address' => $geo->geocoded_address,
                'lat' => $geo->lat,
                'lng' => $geo->lng,
                'formatted_address' => $geo->formatted_address,
                'location_type' => $geo->location_type,
                'partial_match' => $geo->partial_match
            ]
        );

        return $id;
    }

    public static function get($id) {
        return DB::table(GeoCache::$table)->where('id', $id)->first();
    }

    /**
     * @param $address
     * @return mixed
     */
    public static function getByAddress($address) {
        $geo = DB::table(GeoCache::$table)->where('address', $address)->first();
        return $geo;
    }

    public static function update($id,$values) {
        DB::table(GeoCache::$table)->where('id', $id)->update($values);
    }

    public static function findByAddressOrCreateWithNewnessIndicator($address) {

        $geo = GeoCache::getByAddress($address);
        $cached = false;


        Log::debug("Found Geocode Object",["geo_cache"=>$geo]);
        if(!$geo) {
            Log::debug('Address NOT found in geo_cache');
            $resp = Geocode::geocode($address);
            $status = $resp['status'];

            if($status=='OK'){
                $id = GeoCache::create( new GeoCache(
                        [
                            'address' => $address,
                            'geocoded_address' => $address,
                            'lat' => $resp['results'][0]['geometry']['location']['lat'],
                            'lng' => $resp['results'][0]['geometry']['location']['lng'],
                            'formatted_address' => $resp['results'][0]['formatted_address'],
                            'location_type' => $resp['results'][0]['geometry']['location_type'],
                            'partial_match' => Geocode::getPartialMatchFromResponse($resp)
                        ]
                    )
                );
                $geo = GeoCache::get($id);
            }
            else
            {
                Log::error('geocoding failed', ['address'=>$address, 'response'=>$resp]);
            }
        }
        else
        {
            Log::debug('Address found in geo_cache');
            $cached = true;
            $status = "LOCALLY_CACHED";
        }

        return [$geo, $cached, $status];
    }

    public static function findByAddressOrCreate($address) {
        list ($geo, $cached, $status) = self::findByAddressOrCreateWithNewnessIndicator($address);
        return $geo;
    }

    public static function partialMatch($partial_match) {
        $partial_matches = DB::table(self::$table)->where('partial_match', $partial_match)->get();
        return $partial_matches;
    }

    public static function getLocationTypes() {
        return DB::table(self::$table)->groupBy('location_type')->lists('location_type');
    }


}
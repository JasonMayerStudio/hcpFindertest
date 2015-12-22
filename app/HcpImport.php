<?php
/**
 * Created by PhpStorm.
 * User: jtsuyuki
 * Date: 7/10/15
 * Time: 2:48 PM
 */

namespace App;

use DB;
use Log;


class HcpImport extends Hcp
{
    protected static $table = 'hcp_import';


    public static function batchGeocode($max_records_to_geocode)
    {
        $count = 0;
        $cached_count =0;
        $hcps = DB::table(static::$table)->where('geo_cache_id', null)->take(5000)->get();
        $hcps_not_geocoded = [];


        foreach ($hcps as $hcp) {
            Log::debug("HCP ID:" . $hcp->hcp_id);

            list ($geo, $cached, $status) = GeoCache::findByAddressOrCreateWithNewnessIndicator(Hcp::getGeocodeAddress($hcp));
            if ($status == 'OK' || $status == 'LOCALLY_CACHED' ) {
                HcpImport::update($hcp->hcp_id, [
                    'lat' => $geo->lat,
                    'lng' => $geo->lng,
                    'geo_cache_id' => $geo->id,
                    'geo_address' => $geo->address
                ]);
                Log::debug("Geocode OK:", ['hcp_id' => $hcp->hcp_id, 'status'=> $status ]);
            } else {
                Log::error("Geocode Error:" , ['hcp_id' => $hcp->hcp_id, 'address' => Hcp::getGeocodeAddress($hcp), 'status'=> $status ] );
                $hcps_not_geocoded[] = $hcp;
            }


            if (!$cached) {
                Log::debug("{$count} new geocodes");
                $count++;
            } else {
                Log::debug("{$cached_count} cached geocodes");
                $cached_count++;
            }

            if ($count > $max_records_to_geocode) {
                break;
            }
        }

        return $hcps_not_geocoded;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: jtsuyuki
 * Date: 7/10/15
 * Time: 2:47 PM
 */

namespace App;

use DB;


class Hcp
{
    protected static $table = 'hcp';

    /**
     * @return string
     */
    public static function getTable()
    {
        return static::$table;
    }

    public static function getAll() {
        return DB::table(static::$table)->get();
    }

    public static function getAllPaginated($pageSize) {
        return DB::table(static::$table)->paginate($pageSize);
    }

    public static function get($hcp_id) {
        return DB::table(static::$table)->where('hcp_id',$hcp_id)->first();
    }

    public static function update($hcp_id, $values) {
        DB::table(static::$table)->where('hcp_id', $hcp_id)->update($values);
    }

    public static function count() {
        return DB::table(static::$table)->count();
    }

    public static function getGeocodeAddress($hcp) {
        return "{$hcp->address_line1} {$hcp->address_line2} {$hcp->city}, {$hcp->state} {$hcp->zip_code}";
    }

    public static function truncate() {
        $truncate_statement = "TRUNCATE `".static::$table."`;";
        DB::statement($truncate_statement);
    }

    public static function getHcpByPartialMatch ($partial_match, $page_size) {
        return DB::table(static::$table)
            ->join(GeoCache::getTable(), GeoCache::getTable().'.id', '=', static::$table.'.geo_cache_id')
            ->where(GeoCache::getTable().'.partial_match', $partial_match)
            ->paginate($page_size);
    }

    public static function getHcpByLocationType ($location_type, $page_size) {
        return DB::table(static::$table)
            ->join(GeoCache::getTable(), GeoCache::getTable().'.id', '=', static::$table.'.geo_cache_id')
            ->where(GeoCache::getTable().'.location_type', $location_type)
            ->paginate($page_size);
    }
}
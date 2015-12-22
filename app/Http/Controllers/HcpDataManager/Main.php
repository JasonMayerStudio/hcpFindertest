<?php

namespace App\Http\Controllers\HcpDataManager;

use App\GeoCache;
use DB;
use Log;
//use App\User;
use App\Geocode;
use App\HcpImport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class Main
 * @package App\Http\Controllers\HcpDataManager
 */
class Main extends Controller
{
    var $page_size = 100;

    /**
     * Home page
     */
    public function index()
    {
        return view('data.index');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function view_all()
    {
        $hcps = HcpImport::getAllPaginated($this->page_size);

        return view('data.view_list', ['hcps'=>$hcps]);

    }

    /**
     * @param $hcp_id
     * @return \Illuminate\View\View
     */
    public function geocode_hcp($hcp_id)
    {
        $hcp = HcpImport::get($hcp_id);
        $address = HcpImport::getGeocodeAddress($hcp);
        $message = null;
        $geo = null;

        if ($hcp) {
            $geo = GeoCache::findByAddressOrCreate($address);
            if ($geo) {
                HcpImport::update($hcp_id,
                    [
                        'lat' => $geo->lat,
                        'lng' => $geo->lng,
                        'geo_cache_id' => $geo->id,
                        'geo_address' => $geo->address
                    ]
                );
                $message = "geolocation success";
            } else {
                $message = "geolocating failed";
            }

            $hcp = HcpImport::get($hcp_id);
        }
        else
        {
            $message = "HCP ID:{$hcp_id} not found";
        }

        return view("data.geolocate",
        [
            'hcp' => $hcp,
            'geo' => $geo,
            'message' => $message,
            'api_key' => Geocode::$google_api_key
        ]);
    }

    /**
     * @param $hcp_id
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function force_geocode_hcp($hcp_id, Request $request) {
        $hcp = HcpImport::get($hcp_id);
        $hcp_address = HcpImport::getGeocodeAddress($hcp);
        $geo = null;
        $geo_address = $request->input('address');

        if($hcp) {
            $resp = Geocode::geocode($geo_address);
            if($resp['status']=='OK') {
                $id = GeoCache::create( new GeoCache(
                    [
                        'address' => $hcp_address,
                        'geocoded_address' => $geo_address,
                        'lat' => $resp['results'][0]['geometry']['location']['lat'],
                        'lng' => $resp['results'][0]['geometry']['location']['lng'],
                        'formatted_address' => $resp['results'][0]['formatted_address'],
                        'location_type' => $resp['results'][0]['geometry']['location_type'],
                        'partial_match' => Geocode::getPartialMatchFromResponse($resp)
                    ]
                ));
                $geo = GeoCache::get($id);
                HcpImport::update($hcp_id,
                    [
                        'lat' => $geo->lat,
                        'lng' => $geo->lng,
                        'geo_cache_id' => $geo->id,
                        'geo_address' => $geo->address
                    ]
                );
                $hcp = HcpImport::get($hcp_id);
                $message = "Geolocating succeeded";

            } else {
                $message = "Geolocating failed";
            }

        } else {
            $message = "HCP ID:{$hcp_id} not found";
        }

        return view("data.geolocate",
            [
                'hcp' => $hcp,
                'geo' => $geo,
                'message' => $message,
                'api_key' => Geocode::$google_api_key
            ]);

    }

    /**
     * @param $hcp_id
     * @return \Illuminate\View\View
     */
    public function view_hcp($hcp_id)
    {
        $hcp = HcpImport::get($hcp_id);
        $message = "";
        $geo = null;

        if ($hcp) {
            if ($hcp->geo_cache_id) {
                $geo = GeoCache::get($hcp->geo_cache_id);

            } else {
                $message = "this HCP has not been geolocated yet";
            }

        } else {
            $message = "HCP ID:{$hcp_id} not found";
        }

        return view("data.geolocate",
        [
            'hcp' => $hcp,
            'geo' => $geo,
            'message' => $message,
            'api_key' => Geocode::$google_api_key
        ]);

    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function update_geocache($id, Request $request)
    {
        $address = $request->input('address');
        $hcp_id = $request->input('hcp_id');
        $resp = Geocode::geocode($address);


        if($resp['status']=='OK'){

            GeoCache::update($id,
                [
                    'geocoded_address' => $address,
                    'lat' => $resp['results'][0]['geometry']['location']['lat'],
                    'lng' => $resp['results'][0]['geometry']['location']['lng'],
                    'formatted_address' => $resp['results'][0]['formatted_address'],
                    'location_type' => $resp['results'][0]['geometry']['location_type'],
                    'partial_match' => Geocode::getPartialMatchFromResponse($resp)
                ]
            );
        }
        else
        {
            Log::error('geocoding failed', ['address'=>$address, 'response'=>$resp]);
        }
        return $this->geocode_hcp($hcp_id);

    }

    public function view_partial_matches() {
        $hcps =  HcpImport::getHcpByPartialMatch(true, $this->page_size);
        return  view('data.view_list', ['hcps' => $hcps]);
    }

    public function view_by_location_type($selected_type = 'APPROXIMATE') {

        $types = GeoCache::getLocationTypes();
        $types_select = [];
        foreach ($types as $type) {
            $types_select[$type] = $type;
        }

        $hcps = HcpImport::getHcpByLocationType($selected_type, $this->page_size);

        return  view('data.view_list', ['hcps' => $hcps, 'types'=>$types_select, 'selected_type' => $selected_type]);

    }

    public function batch_geocode() {
        $not_geocoded =  HcpImport::batchGeocode(300);

        return view('data.view_list', ['hcps'=>$not_geocoded]);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function truncate()
    {

        $pre_count = HcpImport::count();
        HcpImport::truncate();
        $post_count = HcpImport::count();

        $view_data = [
            'pre_count' => $pre_count,
            'post_count' => $post_count,
            'db' => HcpImport::getTable()
        ];

        return view('data.truncate', ['data' => $view_data]);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function import()
    {
        $import_path = "/var/www/vhosts/dev1/docroot/import/";
        // it appears there are some unexpected commas in the data so csv is difficult to fully parse
        //$import_file = "bup_providers_data.csv";
        // using pipe delimeted works better
        $import_file = "bup_providers_data-pipe.txt";
        $terminator = "|";
        $file = $import_path . $import_file;
        $import_statement = "LOAD DATA INFILE '$file' INTO TABLE ".HcpImport::getTable()." FIELDS TERMINATED BY '{$terminator}' OPTIONALLY ENCLOSED BY '\"'
LINES TERMINATED BY '\n' IGNORE 2 LINES (first_name,m_name,last_name,suffix,address_line1,address_line2,city,state,zip_code,phone)";

        // import statement:
        // LOAD DATA INFILE '/var/www/vhosts/dev1/docroot/import/bup_providers_data-pipe.txt' INTO TABLE hcp_import FIELDS TERMINATED BY '|' OPTIONALLY ENCLOSED BY '\"' ESCAPED BY '' LINES TERMINATED BY '\n' IGNORE 2 LINES (first_name,m_name,last_name,suffix,address_line1,address_line2,city,state,zip_code,phone)
        // fails from PHP due to some driver/buffering issue, haven't found a solution yet.


        // Field order from SAMSHA csv
        // First Name,Middle Name,Last Name,Suffix,Address Line 1,Address Line2,City,State,Zip Code,Phone

        DB::statement($import_statement);

        $view_data = [
            'file' => $file,
            'sql' => $import_statement
        ];

        return view('data.import', ['data' => $view_data]);
    }

}
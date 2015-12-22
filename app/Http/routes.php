<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/data/import', ['uses' => 'HcpDataManager\Main@import', 'as' => 'data_import']);

Route::get('/data/truncate', ['uses' => 'HcpDataManager\Main@truncate', 'as' => 'data_truncate']);

Route::get('/data/view-all', ['uses' => 'HcpDataManager\Main@view_all', 'as' => 'data_view_all']);

Route::get('/data/geolocate-hcp/{id}', ['uses' => 'HcpDataManager\Main@view_hcp', 'as' => 'view_hcp']);
Route::post('/data/geolocate-hcp/{id}', ['uses' => 'HcpDataManager\Main@geocode_hcp', 'as' => 'geolocate_hcp']);


Route::post('/data/update_geocache/{id}', ['uses' => 'HcpDataManager\Main@update_geocache', 'as' => 'update_geocache']);
Route::post('/data/new_geocache/{id}', ['uses' => 'HcpDataManager\Main@force_geocode_hcp', 'as' => 'new_geocache']);


Route::get('/data/location_type/{type?}', ['uses' => 'HcpDataManager\Main@view_by_location_type', 'as' => 'view_by_location_type']);

Route::get('/data/batch_geocode}', ['uses' => 'HcpDataManager\Main@batch_geocode', 'as' => 'batch_geocode']);

Route::get('/data/partial_match}', ['uses' => 'HcpDataManager\Main@view_partial_matches', 'as' => 'view_partial_matches']);

Route::get('/data', ['uses' => 'HcpDataManager\Main@index', 'as' => 'data_home']);

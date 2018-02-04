<?php
use Illuminate\Support\Facades\Cache;

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/wallet_status', "HostsController@walletStatus");
Route::get('/hosts/{mode}', "HostsController@index");
Route::get('/map', "HostsController@map");
Route::get('/versions', "HostsController@versions");
Route::get('/countries', "HostsController@countries");
Route::get('/continents', "HostsController@continents");
Route::get('/network', "HostsController@network");
Route::get('/host/{id}', "HostsController@host");
Route::get('/sia/ticker', function () {
    if (Cache::has('cmcticker')) {
        return Cache::get('cmcticker');
    } else {
        $cmc = file_get_contents('https://api.coinmarketcap.com/v1/ticker/siacoin/');
        $cmc = json_decode($cmc, true);
        Cache::put('cmcticker', $cmc[0], 10);

        return $cmc[0];
    }
});

Route::get('/sia/release', function () {
    if (!Cache::has('siarelease')) {
        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', 'https://api.github.com/repos/NebulousLabs/Sia/releases/latest');
            $response = json_decode($res->getBody(), true);
            Cache::put('siarelease', $response, 24*60);
        } catch(Exception $e) {
        }
    }

    return Cache::get('siarelease');
});

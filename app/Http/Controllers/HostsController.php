<?php

namespace App\Http\Controllers;

use App\Models\Host;
use App\Models\NetworkHistory;
use Illuminate\Support\Facades\Cache;
use GeoIp2\Database\Reader;
use Illuminate\Http\Request;

class HostsController extends Controller
{
    public function walletStatus()
    {
        return response()->json(Cache::get('wallet_online'));
    }

    public function index($mode)
    {
        $cache_key = "hosts_".$mode;

        if (!Cache::has($cache_key)) {
            $hosts = new Host;
            if ($mode == "active") {
                $hosts = $hosts->where('active', 1);
            }

            if ($mode == "latest") {
                $hosts = $hosts->orderBy('id', 'desc')->take(10);
            }
            Cache::put($cache_key, json_encode($hosts->get()), 5);
        }

        return Cache::get($cache_key);
    }

    public function host($id, Request $request)
    {
        $cache_key = "host_".$id;

        if (!Cache::has($cache_key)) {
            if (is_numeric($id)) {
                $host = Host::with(['history' => function ($query) use ($request) {
//                    $query->take(1000);
                    // if (!empty($request->input('xmin')) && !empty($request->input('xmax'))) {
                    //     return $query->whereBetween('created_at', [\Carbon\Carbon::createFromTimestamp($request->input('xmin')), new \Carbon\Carbon::createFromTimestamp($request->input('xmax'))]);
                    // }
                    return $query;
                }])->find($id);
            } else {
                $host = Host::with('history', function($q){
                    //$q->take(1000);
                })->where('key', $id)->first();
            }

            Cache::put($cache_key, json_encode($host), 10);
        }

        return Cache::get($cache_key);
    }

    public function map(Request $request)
    {
        $cache_key = "map_active";
        if (!empty($request->input('id'))) {
            $cache_key = "map_point".$request->input('id');
        }

        if (!Cache::has($cache_key)) {
            $reader = new Reader(env('MAXMIND_DB'));

            $points = [];
            $hosts = Host::where('active', 1);
            if (!empty($request->input('id'))) {
                $hosts = Host::where('id', $request->input('id'));
            }

            foreach ($hosts->get() as $host) {
                try {
                    $info = $reader->city(str_replace(['[',']'], '', $host->host));
                    if ($info) {
                        $points[] = ['host' => $host, 'lat' => $info->location->latitude, 'lng' => $info->location->longitude];
                        if (empty($host->continent) || $host->country) {
                            $host->continent = $info->continent->names['en'];
                            $host->country = $info->country->name;
                            $host->save();
                        }
                    }
                } catch (\Exception $e) {
                    //var_dump($e->getMessage());
                }
            }
            Cache::put($cache_key, $points, 5);
        }

        return Cache::get($cache_key);
    }

    public function versions()
    {
        $cache_key = "versions_active";

        if (!Cache::has($cache_key)) {
            $hosts = Host::selectRaw('count(*) as hosts, version')
                          ->where('active', 1)
                          ->groupBy('version')
                          ->orderBy('version', 'desc')
                          ->get();
            Cache::put($cache_key, json_encode($hosts), 5);
        }

        return Cache::get($cache_key);
    }

    public function countries()
    {
        $cache_key = "countries_active";

        if (!Cache::has($cache_key)) {
            $hosts = Host::selectRaw('count(*) as hosts, country')
                          ->where('active', 1)
                          ->groupBy('country')
                          ->orderBy('hosts', 'desc')
                          ->get();
            Cache::put($cache_key, json_encode($hosts), 5);
        }

        return Cache::get($cache_key);
    }

    public function continents()
    {
        $cache_key = "continents_active";

        if (!Cache::has($cache_key)) {
            $hosts = Host::selectRaw('count(*) as hosts, continent')
                          ->where('active', 1)
                          ->groupBy('continent')
                          ->orderBy('hosts', 'desc')
                          ->get();
            Cache::put($cache_key, json_encode($hosts), 5);
        }

        return Cache::get($cache_key);
    }

    public function network()
    {
        $cache_key = "network_active";

        if (!Cache::has($cache_key)) {
            $hosts = NetworkHistory::orderBy('created_at', 'asc')->get();
            Cache::put($cache_key, json_encode($hosts), 5);
        }

        return Cache::get($cache_key);
    }

    public function recommendedSettings()
    {
        $cache_key = "recommendedSettings";

//        if (!Cache::has($cache_key)) {
            $hosts = Host::where('active', 1)
                         ->orderBy('rank', 'asc')
                         ->take(50)
                         ->get();


            //maxduration:          25 Weeks
            //	maxdownloadbatchsize: 17.83 MB
            //	maxrevisebatchsize:   17.83 MB
            //	netaddress:           sanasol.sknt.ru:9982 (manually specified)
            //	windowsize:           24 Hours
            //
            //	collateral:       200 SC / TB / Month
            //	collateralbudget: 1 MS
            //	maxcollateral:    5 KS Per Contract
            //
            //	mincontractprice:          1 mS
            //	mindownloadbandwidthprice: 1 SC / TB
            //	minstorageprice:           40 SC / TB / Month
            //	minuploadbandwidthprice:   1 MS / TB

            function minmaxavg($hosts, $field){
                return [
                    'min' => $hosts->min($field),
                    'avg' => $hosts->avg($field),
                    'max' => $hosts->max($field),
                    'median' => $hosts->median($field),
                ];
            }

            $settings = [];
            $settings['maxduration'] = minmaxavg($hosts, 'maxduration');
            $settings['maxdownloadbatchsize'] = minmaxavg($hosts,'maxdownloadbatchsize');
            $settings['windowsize'] = minmaxavg($hosts,'windowsize');
            $settings['collateral'] = minmaxavg($hosts,'collateral');
            $settings['collateralbudget'] = minmaxavg($hosts,'collateralbudget');
            $settings['maxcollateral'] = minmaxavg($hosts,'maxcollateral');
            $settings['contractprice'] = minmaxavg($hosts,'contractprice');
            $settings['downloadbandwidthprice'] = minmaxavg($hosts,'downloadbandwidthprice');
            $settings['storageprice'] = minmaxavg($hosts,'storageprice');
            $settings['uploadbandwidthprice'] = minmaxavg($hosts,'uploadbandwidthprice');

            Cache::put($cache_key, json_encode($settings), 5);
//        }

        return Cache::get($cache_key);
    }
}

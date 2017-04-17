<?php

namespace App\Http\Controllers;

use App\Models\Host;
use App\Models\NetworkHistory;
use Illuminate\Support\Facades\Cache;

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
            Cache::put($cache_key, $hosts->get(), 5);
        }

        return Cache::get($cache_key);
    }

    public function host($id)
    {
        if (is_numeric($id)) {
            $host = Host::with('history')->find($id);
        } else {
            $host = Host::with('history')->where('key', $id)->first();
        }
        return $host;
    }

    public function map()
    {
        $cache_key = "map_active";

        if (!Cache::has($cache_key)) {
            $points = [];
            $hosts = Host::where('active', 1);
            foreach ($hosts->get() as $host) {
                $info = geoip_record_by_name($host->host);
                if ($info) {
                    $points[] = ['host' => $host, 'lat' => $info['latitude'], 'lng' => $info['longitude']];
                    if (empty($host->continent) || $host->country) {
                        $host->continent = $info['continent_code'];
                        $host->country = $info['country_name'];
                        $host->save();
                    }
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
            Cache::put($cache_key, $hosts, 5);
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
            Cache::put($cache_key, $hosts, 5);
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
            Cache::put($cache_key, $hosts, 5);
        }

        return Cache::get($cache_key);
    }

    public function network()
    {
        $cache_key = "network_active";

        if (!Cache::has($cache_key)) {
            $hosts = NetworkHistory::orderBy('created_at', 'asc')->get();
            Cache::put($cache_key, $hosts, 5);
        }

        return Cache::get($cache_key);
    }
}

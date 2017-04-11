<?php

namespace App\Console\Commands;

use App\Models\Host;
use App\Models\NetworkHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class Network extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate network stats';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $hosts = Host::where('active', 1)->get();

            $totalstorage = $hosts->reduce(function ($carry, $item) {
                return $carry + $item->totalstorage;
            });

            $remainingstorage = $hosts->reduce(function ($carry, $item) {
                return $carry + $item->remainingstorage;
            });

            $price = $hosts->reduce(function ($carry, $item) {
                return $carry + $item->storageprice/1e12*4320;
            });

            $avg_price = $price/count($hosts);
            $min_price = $hosts->min('storageprice')/1e12*4320;
            $max_price = $hosts->max('storageprice')/1e12*4320;

            $history = new NetworkHistory();
            $history->all_hosts = Host::count();
            $history->active_hosts = Host::where('active', 1)->count();
            $history->remainingstorage = $remainingstorage;
            $history->totalstorage = $totalstorage;
            $history->min_storageprice = $min_price;
            $history->avg_storageprice = $avg_price;
            $history->max_storageprice = $max_price;
            $history->created_at = date('Y-m-d H:i:s');
            $history->save();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

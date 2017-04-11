<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkHistory extends Model
{
    public $timestamps = false;
    protected $table = 'network_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'remainingstorage',
        'totalstorage',
        'min_storageprice',
        'avg_storageprice',
        'max_storageprice',
        'active_hosts',
        'all_hosts',
    ];
}

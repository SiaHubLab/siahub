<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostHistory extends Model
{
    protected $table = 'hosts_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'host_id',
        'remainingstorage',
        'totalstorage',
        'contractprice',
        'downloadbandwidthprice',
        'storageprice',
        'uploadbandwidthprice',
    ];

    public function host()
    {
        return $this->belongsTo(Host::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Host extends Model
{
    protected $table = 'hosts';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'algorithm',
        'key',
        'acceptingcontracts',
        'maxdownloadbatchsize',
        'maxduration',
        'maxrevisebatchsize',
        'netaddress',
        'remainingstorage',
        'sectorsize',
        'totalstorage',
        'unlockhash',
        'windowsize',
        'collateral',
        'maxcollateral',
        'contractprice',
        'downloadbandwidthprice',
        'storageprice',
        'uploadbandwidthprice',
        'revisionnumber',
        'version',
        'firstseen',
        'historicdowntime',
        'historicuptime',
    ];

    public function history()
    {
        return $this->hasMany(HostHistory::class);
    }
}

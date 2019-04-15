<?php

namespace App\Models;

use App\Scopes\VersionScope;
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


    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new VersionScope);
    }

    public function history()
    {
        return $this->hasMany(HostHistory::class);
    }
}

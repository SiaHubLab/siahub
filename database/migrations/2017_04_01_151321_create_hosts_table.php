<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::create('hosts', function (Blueprint $table) {
             $table->increments('id');
             $table->string("algorithm", 255);
             $table->string("key", 255);
             $table->boolean("acceptingcontracts");
             $table->integer("maxdownloadbatchsize");
             $table->integer("maxduration");
             $table->integer("maxrevisebatchsize");
             $table->string("netaddress", 255);
             $table->string("remainingstorage", 255);
             $table->integer("sectorsize");
             $table->string("totalstorage", 255);
             $table->string("unlockhash", 255);
             $table->integer("windowsize");
             $table->string("collateral", 255);
             $table->string("maxcollateral", 255);
             $table->string("contractprice", 255);
             $table->string("downloadbandwidthprice", 255);
             $table->string("storageprice", 255);
             $table->string("uploadbandwidthprice", 255);
             $table->integer("revisionnumber");
             $table->string("version", 255);
             $table->integer("firstseen");
             $table->integer("historicdowntime");
             $table->integer("historicuptime");

             $table->boolean("active")->default(false);
             $table->integer("last_seen")->default(0);
             $table->string("host", 255)->default('');
             $table->string("country", 255)->default('');
             $table->string("continent", 255)->default('');

             $table->index('key');
             $table->index('version');
         });

         Schema::create('hosts_history', function (Blueprint $table) {
             $table->integer('host_id')->unsiged();
             $table->index('host_id');
             $table->string("remainingstorage", 255);
             $table->string("totalstorage", 255);
             $table->string("contractprice", 255);
             $table->string("downloadbandwidthprice", 255);
             $table->string("storageprice", 255);
             $table->string("uploadbandwidthprice", 255);
             $table->timestamps();
         });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         Schema::dropIfExists('hosts');
         Schema::dropIfExists('hosts_history');
     }
}

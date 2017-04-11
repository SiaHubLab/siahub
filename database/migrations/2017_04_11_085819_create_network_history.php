<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNetworkHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('network_history', function (Blueprint $table) {
            $table->string("remainingstorage", 255);
            $table->string("totalstorage", 255);
            $table->string("min_storageprice", 255);
            $table->string("avg_storageprice", 255);
            $table->string("max_storageprice", 255);
            $table->integer("active_hosts")->default(0);
            $table->integer("all_hosts")->default(0);
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('network_history');
    }
}

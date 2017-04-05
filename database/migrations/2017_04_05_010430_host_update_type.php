<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HostUpdateType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn('historicdowntime');
            $table->dropColumn('historicuptime');
        });

        Schema::table('hosts', function (Blueprint $table) {
            $table->string('historicdowntime');
            $table->string('historicuptime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn('historicdowntime');
            $table->dropColumn('historicuptime');
        });

        Schema::table('hosts', function (Blueprint $table) {
            $table->integer("historicdowntime");
            $table->integer("historicuptime");
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpatialRefSys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TABLE spatial_ref_sys (
                `SRID` int(11) NOT NULL,
                `AUTH_NAME` varchar(256) DEFAULT NULL,
                `AUTH_SRID` int(11) DEFAULT NULL,
                `SRTEXT` varchar(2048) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spatial_ref_sys');
    }
}

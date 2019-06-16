<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeometryColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TABLE `geometry_columns` (
                `F_TABLE_CATALOG` varchar(256) DEFAULT NULL,
                `F_TABLE_SCHEMA` varchar(256) DEFAULT NULL,
                `F_TABLE_NAME` varchar(256) NOT NULL,
                `F_GEOMETRY_COLUMN` varchar(256) NOT NULL,
                `COORD_DIMENSION` int(11) DEFAULT NULL,
                `SRID` int(11) DEFAULT NULL,
                `TYPE` varchar(256) NOT NULL
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
        Schema::dropIfExists('geometry_columns');
    }
}

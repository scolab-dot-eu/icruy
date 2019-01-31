<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpatialRefSysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::unprepared("
            INSERT INTO `camineria`.`spatial_ref_sys`
                (`SRID`,
                `AUTH_NAME`,
                `AUTH_SRID`,
                `SRTEXT`)
                VALUES
                (0,
                'ICR',
                '0',
                'GEOGCS[\"GCS_WGS_1984\",DATUM[\"D_WGS_1984\",SPHEROID[\"WGS_1984\",6378137,298.257223563]],PRIMEM[\"Greenwich\",0],UNIT[\"Degree\",0.017453292519943295]]')
        ");
    }
}

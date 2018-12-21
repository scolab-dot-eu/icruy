<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrBadenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cr_baden', function (Blueprint $table) {
            $table->increments('id');
            $table->point('thegeom');
            $table->string('status', 23);
            $table->string('departamento', 4);
            $table->string('codigo_camino', 8)->nullable();
            $table->string('rodadura')->nullable();
            $table->string('estado_de_conservacion')->nullable();
            $table->string('dimensiones')->nullable();
            $table->string('observaciones', 255)->nullable();
            $table->index('codigo_camino');
            $table->index(['departamento', 'codigo_camino']);
            $table->timestamps();
            $table->spatialIndex('thegeom');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cr_baden');
    }
}

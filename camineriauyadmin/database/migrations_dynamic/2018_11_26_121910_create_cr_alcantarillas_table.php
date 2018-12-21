<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrAlcantarillasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cr_alcantarillas', function (Blueprint $table) {
            $table->increments('id');
            $table->point('thegeom');
            $table->string('status', 23);
            $table->string('departamento', 4);
            $table->string('codigo_camino', 8)->nullable();
            $table->string('tipo_alcantarilla')->nullable();
            $table->string('rodadura')->nullable();
            $table->string('estado_de_conservacion')->nullable();
            $table->decimal('cantidad_de_bocas', 2, 0)->nullable();
            $table->string('dimensiones')->nullable();
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();
            $table->index('codigo_camino');
            $table->index(['departamento', 'codigo_camino']);
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
        Schema::dropIfExists('cr_alcantarillas');
    }
}

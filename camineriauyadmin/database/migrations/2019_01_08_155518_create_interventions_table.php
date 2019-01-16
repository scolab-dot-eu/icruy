<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterventionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('anyo_interv')->unsigned();
            $table->string('departamento', 4);
            $table->string('codigo_camino', 8);
            $table->string('tipo_elem', 255)->nullable(); // alcantarilla, puente, etc
            $table->integer('id_elem')->unsigned()->nullable();
            $table->decimal('longitud', 3, 2)->nullable();
            $table->string('tarea', 255);
            $table->string('financiacion', 3);
            $table->string('forma_ejecucion', 3);
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
        Schema::dropIfExists('interventions');
    }
}

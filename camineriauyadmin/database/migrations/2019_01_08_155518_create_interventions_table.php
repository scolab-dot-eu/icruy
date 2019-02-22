<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\ChangeRequest;

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
            $table->string('status', 23)->default(ChangeRequest::FEATURE_STATUS_PENDING_CREATE);
            $table->integer('anyo_interv')->unsigned();
            $table->string('departamento', 4);
            $table->string('codigo_camino', 8);
            $table->string('tipo_elem', 255)->nullable(); // alcantarilla, puente, etc
            $table->integer('id_elem')->unsigned()->nullable();
            $table->decimal('longitud', 3, 2)->nullable();
            $table->decimal('monto', 12, 2);
            $table->string('tarea', 255);
            $table->string('financiacion', 3);
            $table->string('forma_ejecucion', 3);
            $table->timestamps();
            $table->foreign('departamento')->references('code')->on('departments');
            $table->index(['status', 'codigo_camino', 'id_elem']);
            $table->index(['departamento', 'status', 'codigo_camino', 'id_elem']);
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

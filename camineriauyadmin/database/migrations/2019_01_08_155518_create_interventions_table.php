<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\ChangeRequest;
use App\EditableLayerDef;
use App\Intervention;

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
            //$table->integer('status_code')->unsigned()->default(ChangeRequest::STATUS_PENDING);
            $table->string('status', 23)->default(ChangeRequest::FEATURE_STATUS_PENDING_CREATE)->nullable();
            $table->string('departamento', 4);
            $table->string('codigo_camino', 8);
            $errors = [];
            $fieldsDef = json_decode(Intervention::FIELD_DEF);
            $ignoredFields = [
                'id',
                'status',
                'departamento',
                'codigo_camino'];
            EditableLayerDef::createFields($table, $fieldsDef, $ignoredFields, $errors);
            $table->foreign('departamento')->references('code')->on('departments');
            $table->index(['status', 'financiacion', 'departamento', 'tarea', 'codigo_camino'], 'idx_sta_fin_dep_tar_cam');
            $table->index(['departamento', 'status', 'codigo_camino', 'financiacion', 'tarea'], 'idx_dep_sta_cam_fin_tar');
            $table->index(['status', 'codigo_camino', 'financiacion', 'tarea'], 'idx_sta_cam_fin_tar');
            $table->index(['status', 'codigo_camino', 'id_elem'], 'idx_sta_cam_elem');
            $table->index(['departamento', 'status', 'codigo_camino', 'id_elem'], 'idx_dep_sta_cam_elem');
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

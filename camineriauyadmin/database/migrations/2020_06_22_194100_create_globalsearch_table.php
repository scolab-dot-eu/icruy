<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\ChangeRequest;
use App\EditableLayerDef;
use App\Camino;

class CreateGlobalSearchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $name = EditableLayerDef::FTSEARCH_TABLE;
            Schema::create($name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('layer', 255);
                $table->integer('feat_id');
                $table->string('nombre', 255);
                $table->text('texto');
                $table->string('departamento', 4);
                $table->string('codigo_camino', 8);
                $table->foreign('departamento')->references('code')->on('departments');
                $table->index(['departamento', 'layer']);
            });
                $layers = EditableLayerDef::all();
                foreach ($layers as $layer) {
                    $tableName = $layer->name;
                    if ($tableName != 'interventions') {
                        $layerFieldDefs = json_decode($layer->fields);
                        $expression = EditableLayerDef::getFTSearchExpression($layerFieldDefs).' as texto, '.EditableLayerDef::getFTSearchNombreExpression().' as nombre_codigo_camino, id, departamento, codigo_camino';
                        foreach (DB::table($tableName)->selectRaw($expression)->get() as $row) {
                            DB::table(EditableLayerDef::FTSEARCH_TABLE)->insert(
                                ['texto' => $row->texto, 'nombre' => $row->nombre_codigo_camino ,'departamento' => $row->departamento, 'codigo_camino' => $row->codigo_camino, 'feat_id' => $row->id, 'layer' => $tableName]
                                );
                        }
                        EditableLayerDef::removeFTSTriggers($tableName);
                        EditableLayerDef::createFTSTriggers($tableName, $layerFieldDefs);
                    }
                }
                DB::statement('CREATE FULLTEXT INDEX '.$name.'_ftidx ON '.$name.'(texto)');
                
        } catch (Exception $e) {
            $this->down();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(EditableLayerDef::FTSEARCH_TABLE);
        EditableLayerDef::removeFTSTriggers(EditableLayerDef::FTSEARCH_TABLE);
    }
}

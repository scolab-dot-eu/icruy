<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChangerequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('changerequests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('requested_by_id')->unsigned();
            $table->integer('validated_by_id')->unsigned()->nullable();
            $table->string('layer', 200);
            $table->string('codigo_camino', 8)->nullable()->default(null);
            $table->integer('feature_id')->unsigned()->nullable();
            $table->string('departamento', 4);
            $table->string('status');
            $table->string('operation');
            $table->json('feature_previous')->nullable();
            $table->json('feature')->nullable();
            $table->timestamps();
            $table->index(['status', 'layer', 'feature_id', 'codigo_camino'], 'idx_chr_sta_lyr_fea_cam');
            $table->index(['status', 'layer', 'feature_id', 'departamento', 'codigo_camino'], 'idx_chr_sta_lyr_fea_dep_cam');
            $table->index(['requested_by_id', 'status', 'layer']);
            $table->index('validated_by_id');
            $table->foreign('departamento')->references('code')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('changerequests');
    }
}

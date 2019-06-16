<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMtopchangerequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mtopchangerequests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('requested_by_id')->unsigned();
            $table->integer('validated_by_id')->unsigned()->nullable();
            $table->string('codigo_camino', 8)->nullable()->default(null);
            $table->integer('feature_id')->unsigned()->nullable()->default(null);
            $table->string('departamento', 4);
            $table->string('status');
            $table->string('operation');
            $table->json('feature_previous')->nullable();
            $table->json('feature')->nullable();
            $table->json('feature_validated')->nullable();
            $table->timestamps();
            $table->index(['status', 'feature_id']);
            $table->index(['requested_by_id', 'status']);
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
        Schema::dropIfExists('mtopchangerequests');
    }
}

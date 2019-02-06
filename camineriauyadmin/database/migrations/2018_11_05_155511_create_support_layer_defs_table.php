<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportlayerdefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supportlayerdefs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200)->unique();
            $table->string('title', 255);
            $table->boolean('isbaselayer')->default(false);
            $table->boolean('visible')->default(false);
            $table->string('layergroup', 200);
            $table->text('url')->nullable();
            $table->text('api_key')->nullable();
            $table->string('protocol');
            $table->boolean('showTable')->default(True);
            $table->boolean('showInSearch')->default(True);
            $table->text('metadata', 200)->nullable();
            $table->json('conf')->nullable();
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
        Schema::dropIfExists('supportlayerdefs');
    }
}

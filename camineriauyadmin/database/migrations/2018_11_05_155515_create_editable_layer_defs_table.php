<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEditablelayerdefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('editablelayerdefs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200)->unique();
            $table->string('title', 255);
            $table->string('geom_type', 10);
            $table->string('protocol', 255);
            $table->text('url', 200);
            $table->json('fields');
            $table->string('geom_style')->nullable();
            $table->json('style')->nullable();
            /*
            $table->boolean('visible');
            $table->boolean('download');
            $table->boolean('showTable');
            $table->boolean('showInSearch');*/
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
        Schema::dropIfExists('editablelayerdefs');
    }
}

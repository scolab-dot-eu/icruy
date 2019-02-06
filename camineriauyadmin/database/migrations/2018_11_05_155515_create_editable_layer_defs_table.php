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
            $table->json('fields');
            $table->string('geom_style')->nullable();
            $table->json('style')->nullable();
            $table->boolean('visible')->default(False);
            $table->boolean('download')->default(True);
            $table->boolean('showTable')->default(True);
            $table->boolean('showInSearch')->default(True);
            $table->text('metadata', 200)->nullable();
            $table->json('conf')->nullable()->default(null);
            $table->boolean('enabled')->default(True);
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

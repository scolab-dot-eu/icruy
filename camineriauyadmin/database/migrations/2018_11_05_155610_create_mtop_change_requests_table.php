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
            $table->integer('processed_by_id')->unsigned()->nullable();
            $table->string('layer', 200);
            $table->string('status');
            $table->string('operation');
            $table->json('feature');
            $table->timestamps();
            
            $table->index('requested_by_id');
            $table->index('processed_by_id');
            $table->index('status');
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

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMtopchangerequestcommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mtopchangerequestcomments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('message');
            $table->integer('user_id')->unsigned();
            $table->integer('mtopchangerequest_id')->unsigned();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('mtopchangerequest_id');
            $table->foreign('mtopchangerequest_id')->references('id')->on('mtopchangerequests');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mtopchangerequestcomments');
    }
}

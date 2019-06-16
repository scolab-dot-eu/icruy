<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 4)->unique();
            $table->string('name')->unique();
            $table->double('minx', 9, 5);
            $table->double('maxx', 9, 5);
            $table->double('miny', 9, 5);
            $table->double('maxy', 9, 5);
            $table->string('layer_name', 254);
            $table->string('color', 254);
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
        Schema::dropIfExists('departments');
    }
}

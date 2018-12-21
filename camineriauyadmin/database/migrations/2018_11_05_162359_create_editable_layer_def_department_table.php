<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEditablelayerdefDepartmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('editablelayerdef_department', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('editablelayerdef_id')->unsigned();
            $table->integer('department_id')->unsigned();
            $table->timestamps();
            
            $table->index('department_id');
            $table->index('editablelayerdef_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('editablelayerdef_department');
    }
}

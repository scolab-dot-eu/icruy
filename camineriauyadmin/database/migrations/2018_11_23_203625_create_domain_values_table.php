<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainvaluesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domainvalues', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table', 255);
            $table->string('field', 255);
            $table->string('code', 255);
            $table->string('def', 255);
            $table->timestamps();
            $table->index(['table', 'field', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domainvalues');
    }
}

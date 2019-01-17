<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartialCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partialcertificates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo', 255)->unique();
            $table->string('codigo_intervencion', 255);
            $table->integer('id_intervencion')->unsigned();
            $table->decimal('monto', 12, 2);
            $table->date('fecha_certificado');
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
        Schema::dropIfExists('partialcertificates');
    }
}

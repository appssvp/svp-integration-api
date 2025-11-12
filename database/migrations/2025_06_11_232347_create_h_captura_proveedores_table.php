<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHCapturaProveedoresTable extends Migration
{
    public function up()
    {
        Schema::create('h_captura_proveedores', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha');
            $table->string('operador')->nullable();
            $table->string('via')->nullable();
            $table->string('motivo')->nullable();
            $table->string('modelo')->nullable();
            $table->string('clasificacion')->nullable();
            $table->string('empresa')->nullable();
            $table->string('proveedor')->nullable();
            $table->string('placa')->nullable();
            $table->integer('cruces')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('h_captura_proveedores');
    }
}

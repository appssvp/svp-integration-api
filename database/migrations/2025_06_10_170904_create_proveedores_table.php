<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProveedoresTable extends Migration
{
    public function up()
    {
        Schema::create('list_proveedores', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_registro');        
            $table->string('proveedor')->nullable(); 
            $table->string('empresa')->nullable();          
            $table->string('motivo_ingreso')->nullable();      
            $table->string('modelo_vehiculo')->nullable();      
            $table->string('clasificacion_vehiculo')->nullable(); 
            $table->string('tag')->nullable();         
            $table->integer('cruces_permitidos')->default(0);  
            $table->timestamps();                 
        });
    }

    public function down()
    {
        Schema::dropIfExists('list_proveedores');
    }
}

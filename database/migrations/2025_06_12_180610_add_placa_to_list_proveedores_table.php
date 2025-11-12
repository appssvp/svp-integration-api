<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlacaToListProveedoresTable extends Migration
{
    public function up()
    {
        Schema::table('list_proveedores', function (Blueprint $table) {
            $table->string('placa')->after('fecha_registro');
        });
    }

    public function down()
    {
        Schema::table('list_proveedores', function (Blueprint $table) {
            $table->dropColumn('placa');
        });
    }
}

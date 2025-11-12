<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTurnoPermitidoToListProveedoresTable extends Migration

{
    public function up()
    {
        Schema::table('list_proveedores', function (Blueprint $table) {
            $table->string('turno_permitido')->nullable()->after('cruces_permitidos');
        });
    }

    public function down()
    {
        Schema::table('list_proveedores', function (Blueprint $table) {
            $table->dropColumn('turno_permitido');
        });
    }
}

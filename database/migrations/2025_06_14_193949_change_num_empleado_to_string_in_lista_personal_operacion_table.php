<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lista_personal_operacion', function (Blueprint $table) {

            // Luego cambiamos el tipo de dato
            $table->string('num_empleado', 10)->change();

        });
    }

    public function down(): void
    {
        Schema::table('lista_personal_operacion', function (Blueprint $table) {
            $table->dropUnique(['num_empleado']);
            $table->integer('num_empleado')->change();
            $table->unique('num_empleado');
        });
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('lista_personal_operacion')) {
            Schema::create('lista_personal_operacion', function (Blueprint $table) {
                $table->id();
                $table->integer('num_empleado')->unique();
                $table->string('nombre', 100);
                $table->string('puesto', 100);
                $table->date('fecha_ingreso')->nullable(); 
            });
        } elseif (!Schema::hasColumn('lista_personal_operacion', 'fecha_ingreso')) {
            Schema::table('lista_personal_operacion', function (Blueprint $table) {
                $table->date('fecha_ingreso')->nullable()->after('puesto'); 
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('lista_personal_operacion', 'fecha_ingreso')) {
            Schema::table('lista_personal_operacion', function (Blueprint $table) {
                $table->dropColumn('fecha_ingreso');
            });
        }
    }
};

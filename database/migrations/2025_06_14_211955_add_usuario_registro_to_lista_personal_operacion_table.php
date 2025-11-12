<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lista_personal_operacion', function (Blueprint $table) {
            $table->string('usuario_registro', 100)->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('lista_personal_operacion', function (Blueprint $table) {
            $table->dropColumn('usuario_registro');
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();  
            $table->string('uuid', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->text('connection')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->text('queue')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->longText('payload')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->longText('exception')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->timestamp('failed_at')->useCurrent();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};
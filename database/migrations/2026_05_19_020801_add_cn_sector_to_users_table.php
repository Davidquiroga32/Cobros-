<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cn')->nullable()->unique()->after('phone')
                  ->comment('Código de cobrador, ej: CN-001');
            $table->foreignId('sector_id')->nullable()->constrained('sectores')->nullOnDelete()->after('cn');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropColumn(['cn', 'sector_id']);
        });
    }
};
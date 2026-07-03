<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curso', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reservado_por');
        });
    }

    public function down(): void
    {
        Schema::table('curso', function (Blueprint $table) {
            $table->foreignId('reservado_por')->nullable()->constrained('usuario')->nullOnDelete()->cascadeOnUpdate();
        });
    }
};

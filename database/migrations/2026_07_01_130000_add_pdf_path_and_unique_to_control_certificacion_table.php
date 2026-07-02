<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('control_certificacion', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('fecha_emision');
            $table->unique('inscripcion_id');
        });
    }

    public function down(): void
    {
        Schema::table('control_certificacion', function (Blueprint $table) {
            $table->dropUnique(['inscripcion_id']);
            $table->dropColumn('pdf_path');
        });
    }
};

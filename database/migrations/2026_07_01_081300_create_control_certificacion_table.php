<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control_certificacion', function (Blueprint $table) {
            $table->id();
            $table->float('nota');
            $table->string('estado_certificacion', 50);
            $table->date('fecha_emision')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
            $table->foreignId('inscripcion_id')->constrained('inscripcion')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_certificacion');
    }
};

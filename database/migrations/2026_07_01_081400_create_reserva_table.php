<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha_reserva')->useCurrent();
            $table->timestamp('fecha_vencimiento');
            $table->string('estado_reserva', 20)->default('pendiente');
            $table->foreignId('usuario_id')->constrained('usuario')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('curso_id')->constrained('curso')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva');
    }
};

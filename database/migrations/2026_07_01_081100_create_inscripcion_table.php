<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_inscripcion');
            $table->string('estado_inscripcion', 20);
            $table->float('monto_total');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
            $table->foreignId('estudiante_id')->constrained('usuario')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('plan_pago_id')->constrained('plan_pago')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('curso_id')->constrained('curso')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion');
    }
};

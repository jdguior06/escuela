<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->float('precio_final');
            $table->string('estado_curso', 20)->default('disponible');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
            $table->foreignId('instructor_id')->constrained('usuario')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('vehiculo_id')->constrained('vehiculo')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('tipo_curso_id')->constrained('tipo_curso')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('franja_horaria_id')->constrained('franja_horaria')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('reservado_por')->nullable()->constrained('usuario')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuota_plan_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('inscripcion')->restrictOnDelete()->cascadeOnUpdate();
            $table->integer('nro_cuota');
            $table->float('monto');
            $table->date('fecha_vencimiento');
            $table->string('estado_cuota', 20)->default('pendiente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuota_plan_pago');
    }
};

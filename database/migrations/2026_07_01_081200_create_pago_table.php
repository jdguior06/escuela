<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->float('monto');
            $table->integer('nro_cuota')->default(1);
            $table->string('id_transaccion', 100)->nullable();
            $table->string('nro_pedido', 50)->nullable();
            $table->string('estado_pago', 20)->default('pendiente');
            $table->string('correo_notificacion', 150)->nullable();
            $table->boolean('notificado')->default(false);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
            $table->foreignId('usuario_id')->constrained('usuario')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('metodo_id')->constrained('metodo_pago')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('inscripcion_id')->constrained('inscripcion')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago');
    }
};

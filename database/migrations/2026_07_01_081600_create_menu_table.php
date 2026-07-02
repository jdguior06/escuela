<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ruta', 150);
            $table->string('icono', 50)->nullable();
            $table->integer('orden')->default(0);
            $table->foreignId('padre_id')->nullable()->constrained('menu')->nullOnDelete();
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};

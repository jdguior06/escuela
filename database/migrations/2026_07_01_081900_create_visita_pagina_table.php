<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visita_pagina', function (Blueprint $table) {
            $table->id();
            $table->string('pagina', 150)->unique();
            $table->integer('contador')->default(0);
            $table->timestamp('actualizado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visita_pagina');
    }
};

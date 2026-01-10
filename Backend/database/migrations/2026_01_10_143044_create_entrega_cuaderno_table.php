<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('entrega_cuaderno', function (Blueprint $table) {
            $table->id();
            $table->date('Fecha_creacion');
            $table->date('Fecha_Limite');
            $table->unsignedBigInteger('ID_Grado');
            $table->foreign('ID_Grado')
                ->references('id')->on('grado')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('entrega_cuaderno');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('alumno_entrega', function (Blueprint $table) {
            $table->id();
            $table->string('URL_Cuaderno', 255);
            $table->date('Fecha_Entrega');
            $table->unsignedBigInteger('ID_Alumno');
            $table->unsignedBigInteger('ID_Entrega');
            $table->foreign('ID_Alumno')
                ->references('id_usuario')->on('alumno')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('ID_Entrega')
                ->references('id')->on('entrega_cuaderno')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('alumno_entrega');
    }
};

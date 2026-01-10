<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('estancia_alumno', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ID_Alumno');
            $table->string('CIF_Empresa',15);
            $table->date('Fecha_inicio');
            $table->date('Fecha_fin')->nullable();

            $table->foreign('ID_Alumno')
                ->references('id_usuario')->on('alumno')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('CIF_Empresa')
                ->references('CIF')->on('empresa')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('estancia_alumno');
    }
};

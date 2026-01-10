<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('nota_competencia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ID_Competencia');
            $table->unsignedBigInteger('ID_Alumno');
            $table->float('Nota',52);

            $table->foreign('ID_Competencia')
                ->references('id')->on('competencia')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('ID_Alumno')
                ->references('id_usuario')->on('alumno')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('nota_competencia');
    }
};

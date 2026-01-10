<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('nota_egibide', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ID_Asignatura');
            $table->unsignedBigInteger('ID_Alumno');
            $table->decimal('nota',4,2);

            $table->foreign('ID_Asignatura')
                ->references('id')->on('asignatura')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('ID_Alumno')
                ->references('id_usuario')->on('alumno')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('nota_egibide');
    }
};

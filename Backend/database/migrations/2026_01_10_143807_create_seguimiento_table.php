<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('seguimiento', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('ID_Estancia');
                    $table->date('Fecha');
                    $table->time('Hora');
                    $table->string('Accion_seguimiento', 150);
                    $table->text('Seguimiento_actividad');
                    $table->unsignedBigInteger('ID_Tutor')->nullable();
                    $table->unsignedBigInteger('ID_Instructor')->nullable();

                    $table->foreign('ID_Estancia')
                        ->references('id')->on('estancia_alumno')
                        ->cascadeOnDelete()
                        ->cascadeOnUpdate();
                    $table->foreign('ID_Tutor')
                        ->references('id_usuario')->on('tutor')
                        ->nullOnDelete()
                        ->cascadeOnUpdate();
                    $table->foreign('ID_Instructor')
                        ->references('id_usuario')->on('instructor')
                        ->nullOnDelete()
                        ->cascadeOnUpdate();
                });
    }

    public function down(): void {
        Schema::dropIfExists('seguimiento');
    }
};

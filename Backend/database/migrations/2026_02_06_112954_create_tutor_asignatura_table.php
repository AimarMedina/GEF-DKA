<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('tutor_asignatura', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('ID_Tutor');
            $table->unsignedBigInteger('ID_Asignatura');

            $table->index('ID_Tutor', 'ix_tutor_asignatura_tutor');
            $table->index('ID_Asignatura', 'ix_tutor_asignatura_asignatura');

            // Un tutor no puede estar asignado dos veces a la misma asignatura
            $table->unique(['ID_Tutor', 'ID_Asignatura'], 'uq_tutor_asignatura');

            // Foreign Key Constraints
            $table->foreign('ID_Tutor', 'fk_tutor_asignatura_tutor')
                ->references('ID_Usuario')
                ->on('tutor')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('ID_Asignatura', 'fk_tutor_asignatura_asignatura')
                ->references('id')
                ->on('asignatura')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('tutor_asignatura');
    }
};

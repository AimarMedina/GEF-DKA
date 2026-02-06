<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('alumno', function (Blueprint $table) {
            // Añadir DNI y MATRÍCULA
            $table->string('DNI', 20)->nullable()->after('ID_Usuario');
            $table->string('Matricula', 50)->nullable()->after('DNI');

            // Índices
            $table->index('DNI', 'ix_alumno_dni');
            $table->index('Matricula', 'ix_alumno_matricula');

            $table->unique('DNI', 'uq_alumno_dni');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('alumno', function (Blueprint $table) {
            $table->dropIndex('ix_alumno_dni');
            $table->dropIndex('ix_alumno_matricula');
            $table->dropUnique('uq_alumno_dni');
            $table->dropColumn(['DNI', 'Matricula']);
        });
    }
};

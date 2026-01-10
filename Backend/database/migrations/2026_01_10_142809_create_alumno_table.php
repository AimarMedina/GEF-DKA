<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
Schema::create('alumno', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario')->primary();
            $table->unsignedBigInteger('ID_Grado')->nullable();
            $table->unsignedBigInteger('ID_Tutor')->nullable();
            $table->unsignedBigInteger('ID_Instructor')->nullable();

            $table->foreign('id_usuario')
                ->references('id')->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('ID_Grado')
                ->references('id')->on('grado')
                ->nullOnDelete()
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
        Schema::dropIfExists('alumno');
    }
};

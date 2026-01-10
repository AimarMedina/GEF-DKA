<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
Schema::create('tutor_instructor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ID_Tutor');
            $table->unsignedBigInteger('ID_Instructor');
            $table->foreign('ID_Tutor')
                ->references('id_usuario')->on('tutor')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('ID_Instructor')
                ->references('id_usuario')->on('instructor')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tutor_instructor');
    }
};

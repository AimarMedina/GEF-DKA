<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
Schema::create('tutor_grado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ID_Tutor');
            $table->unsignedBigInteger('ID_Grado');
            $table->foreign('ID_Tutor')
                ->references('id_usuario')->on('tutor')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('ID_Grado')
                ->references('id')->on('grado')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tutor_grado');
    }
};

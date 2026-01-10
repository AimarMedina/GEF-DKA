<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('horario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ID_Estancia');
            $table->string('Dias',20);
            $table->string('Horario1',20);
            $table->string('Horario2',20);

            $table->foreign('ID_Estancia')
                ->references('id')->on('estancia_alumno')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('horario');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notas_cuaderno', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ID_Cuaderno');
            $table->unsignedBigInteger('ID_Tutor');
            $table->date('Fecha');
            $table->float('Nota', 52);

            $table->foreign('ID_Cuaderno')
                ->references('id')->on('entrega_cuaderno')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('ID_Tutor')
                ->references('id_usuario')->on('tutor')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('notas_cuaderno');
    }
};

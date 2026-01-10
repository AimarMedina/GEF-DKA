<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('asignatura', function (Blueprint $table) {
                    $table->id();
                    $table->string('nombre', 150);
                    $table->unsignedBigInteger('ID_Grado');
                    $table->foreign('ID_Grado')
                        ->references('id')->on('grado')
                        ->cascadeOnDelete()
                        ->cascadeOnUpdate();
                });
    }

    public function down(): void {
        Schema::dropIfExists('asignatura');
    }
};

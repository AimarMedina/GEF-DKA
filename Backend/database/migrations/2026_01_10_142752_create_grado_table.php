<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('grado', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('curso', 50)->nullable();
            $table->unsignedBigInteger('id_tutor')->nullable();
            $table->foreign('id_tutor')
                ->references('id_usuario')->on('tutor')
                ->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('grado');
    }
};

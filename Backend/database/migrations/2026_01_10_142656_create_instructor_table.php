<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('instructor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario')->primary();
            $table->string('CIF_Empresa', 15);
            $table->foreign('id_usuario')
                ->references('id')->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('CIF_Empresa')
                ->references('CIF')->on('empresa')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('instructor');
    }
};

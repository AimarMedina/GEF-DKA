<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tutor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario')->primary();
            $table->foreign('id_usuario')
                ->references('id')->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tutor');
    }
};

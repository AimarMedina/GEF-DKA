<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('competencia', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 255);
            $table->unsignedBigInteger('ID_Grado');
            $table->foreign('ID_Grado')
            ->references('id')
            ->on('grado')
            ->onDelete('cascade');

                        $table->timestamps();

        });
    }

    public function down(): void {
        Schema::dropIfExists('competencia');
    }
};

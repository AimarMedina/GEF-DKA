<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comp_ra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ID_Comp');
            $table->unsignedBigInteger('ID_Ra');
            $table->unsignedBigInteger('ID_Asignatura');

            $table->foreign('ID_Comp')
                ->references('id')->on('competencia')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('ID_Ra')
                ->references('id')->on('ra')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('ID_Asignatura')
                ->references('id')->on('asignatura')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('comp_ra');
    }
};

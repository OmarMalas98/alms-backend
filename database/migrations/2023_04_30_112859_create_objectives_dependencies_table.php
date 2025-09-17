<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('objective_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('objective_id')->nullable();
            $table->unsignedBigInteger('parent_objective_id')->nullable();
            $table->foreign('objective_id')->references('id')->on('learning_objectives')->onDelete('cascade');
            $table->foreign('parent_objective_id')->references('id')->on('learning_objectives')->onDelete('set null');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('node_dependencies');
    }
};

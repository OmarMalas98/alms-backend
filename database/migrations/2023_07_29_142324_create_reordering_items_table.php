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
        Schema::create('reordering_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reordering_question_id');
            $table->string('text');
            $table->integer('order');

            $table->foreign('reordering_question_id')->references('id')->on('reordering_questions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reordering_items');
    }
};

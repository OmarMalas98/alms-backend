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
        Schema::create('blank_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('question_id');
            $table->integer('blank_number');
            $table->text('answer_text');
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('blank_questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blank_answers');
    }
};

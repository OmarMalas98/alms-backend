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
        Schema::create('cross_options_right', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->unsignedBigInteger("cross_question_id");
            $table->foreign("cross_question_id")->references('id')->on("cross_questions")->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cross_options_right');
    }
};

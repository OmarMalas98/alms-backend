<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZoneSelfAssessmentTable extends Migration
{
    public function up()
    {
        Schema::create('zone_self_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('rating'); // Rating from 1 to 5
            $table->timestamps();

            $table->unique(['zone_id', 'user_id']);
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('zone_self_assessments');
    }
}

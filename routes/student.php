<?php


use App\Http\Controllers\CourseController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'role:2'], function () {



    Route::prefix('course')->middleware('auth:api')->group(function () {
        Route::get('/index', [CourseController::class,'all']);
        Route::get('/finished', [CourseController::class, 'getFinishedCourses']);
        Route::get('/enrolled', [CourseController::class, 'enrolledCourses']);
        Route::get('{id}/lessons/', [CourseController::class,'getLessons']);
        Route::get('/{id}', [CourseController::class,'showS']);
        Route::post('{id}/enroll',[CourseController::class, 'enrollInCourse']);
        Route::group(['middleware' => 'enrolledInCourse'], function () {
            Route::get('{id}/reviews', [ReviewController::class, 'getReviews']);
            Route::post('{id}/review', [ReviewController::class, 'create']);
            Route::post('{id}/review/edit', [ReviewController::class, 'update']);
            Route::delete('{id}/review', [ReviewController::class, 'destroy']);
            Route::get('{id}/lessons_graph/', [CourseController::class,'generateZonesGraph']);
            Route::post('{id}/unenroll',[CourseController::class, 'unenrollInCourse']);
        });
    });

    Route::prefix('lesson')->middleware('auth:api')->group(function () {
        Route::get('/{id}', [ZoneController::class, 'show']);
        Route::post('/{id}/next', [ZoneController::class, 'next']);
        Route::get('/{id}/current', [ZoneController::class, 'current']);
        Route::post('/{id}/reset', [ZoneController::class, 'resetProgress']);
        Route::post('/{id}/self-assessment', [ZoneController::class, 'submitSelfAssessment']);
    });


});

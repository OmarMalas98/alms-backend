<?php

use App\Http\Controllers\ComponentControllers\ComponentController;
use App\Http\Controllers\ComponentControllers\PageController;
use App\Http\Controllers\ComponentControllers\TextAreaController;
use App\Http\Controllers\ComponentControllers\TitleController;
use App\Http\Controllers\ComponentControllers\VideoController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LearningObjectiveController;
use App\Http\Controllers\QuestionControllers\Blank\BlankQuestionController;
use App\Http\Controllers\QuestionControllers\Cross\CrossQuestionController;
use App\Http\Controllers\QuestionControllers\MultiChoice\MultiChoiceQuestionController;
use App\Http\Controllers\QuestionControllers\QuestionController;
use App\Http\Controllers\ReorderingQuestionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['role:1','auth:api']], function () {

    Route::get('/dashboard', [UserController::class, 'dashboard']);

    Route::prefix('course')->middleware('auth:api')->group(function () {
        Route::get('/enrolled-users/{id}', [UserController::class, 'enrolledUsers']);
        Route::get('/enrolled-users', [UserController::class, 'enrolledUsers']);
        Route::get('/index', [CourseController::class, 'index']);
        Route::get('/{id}', [CourseController::class, 'show']);
        Route::post('/add', [CourseController::class, 'store']);
        Route::post('/update/{id}', [CourseController::class, 'update']);
        Route::delete('/delete/{id}', [CourseController::class, 'destroy']);
        Route::post('{id}/admins',[CourseController::class, 'addToAdmins']);
        Route::get('/graph/{id}', [CourseController::class,'generateObjectivesGraph']);
        Route::get('{id}/lessons/', [CourseController::class,'getLessons']);

    });

    Route::prefix('page')->middleware('auth:api')->group(function () {
            Route::get('/{id}', [PageController::class, 'show']);
            Route::post('/update/{id}', [PageController::class, 'update']);
            Route::delete('/{id}', [PageController::class, 'destroy']);
//    Route::get('/lesson/{id}', [PageController::class, 'pagesOfLesson'])->middleware('content_admin');
        // Route::get('/lesson/{id}', [PageController::class, 'pagesOfLesson']);
        Route::post('/add', [PageController::class, 'store'])->middleware('parent_admin');
    });

    Route::prefix('component')->middleware('auth:api')->group(function () {
        Route::get('/index/{id}', [ComponentController::class, 'index']);
        Route::middleware('component_parent_owner')->group(function () {
            Route::post('/update/{id}', [ComponentController::class, 'update']);
            Route::delete('/delete/{id}', [ComponentController::class, 'destroy']);
            Route::post('/move/{id}', [ComponentController::class, 'moveComponent'])->middleware('component_owner');
            Route::get('/suggestNew/{id}',[ComponentController::class, 'generate']);
            Route::post('/respond/{id}',[ComponentController::class, 'respondToGen']);

        });

    });

    Route::prefix('lesson')->middleware('auth:api')->group(function () {
        Route::post('/add', [ZoneController::class, 'store']);
        Route::post('/update/{id}', [ZoneController::class, 'update']);
        Route::get('{id}/pages', [ZoneController::class, 'pagesOfZone']);
        Route::get('{id}/show', [ZoneController::class, 'showPages']);
        Route::get('{id}/show', [ZoneController::class, 'showPages']);
        Route::get('{id}/objectives', [ZoneController::class, 'getObjectives']);
        Route::get('{id}/objectives-tree', [ZoneController::class, 'getObjectivesTree']);
        Route::get('{id}/available-objectives', [ZoneController::class, 'getAvailableObjectives']);
    });

    Route::prefix('question')->middleware('auth:api')->group(function () {
        Route::get('/index/{id}', [QuestionController::class, 'index']);
        Route::post('/update/{id}', [QuestionController::class, 'update']);
        Route::delete('/delete/{id}', [QuestionController::class, 'destroy']);
    });

    Route::prefix('cross-question')->group(function () {
        // Route::post('/add', [QuestionController::class, 'store']);
        Route::get('/show/{id}', [CrossQuestionController::class, 'show']);
        Route::post('/answer/{id}', [CrossQuestionController::class, 'attempt']);
        Route::post('/update/{id}', [CrossQuestionController::class, 'update']);
    });
    Route::prefix('blank-question')->group(function () {
        // Route::post('/add', [QuestionController::class, 'store']);
        Route::get('/show/{id}', [BlankQuestionController::class, 'show']);
        Route::post('/answer', [BlankQuestionController::class, 'answer']);
    });
    Route::prefix('reordering-question')->group(function () {
        // Route::post('/add', [QuestionController::class, 'store']);
        Route::get('/show/{id}', [ReorderingQuestionController::class, 'show']);
        Route::post('/answer/{id}', [ReorderingQuestionController::class, 'answer']);
        Route::post('/update/{id}', [ReorderingQuestionController::class, 'update']);
    });

    Route::prefix('video')->group(function () {
        Route::post('/add', [VideoController::class, 'store']);
    });

    Route::prefix('textarea')->group(function () {
        Route::post('/add', [TextAreaController::class, 'store']);
    });

    Route::prefix('title')->group(function () {
        Route::post('/add', [TitleController::class, 'store']);
    });
    Route::prefix('learning-objective')->middleware('auth:api')->group(function () {
        Route::get('/index', [LearningObjectiveController::class, 'index']);
        Route::post('/add', [LearningObjectiveController::class, 'store']);
        Route::post('/{id}/add-parent',[LearningObjectiveController::class,'addParent']);
        Route::get('/{id}/available-parents',[LearningObjectiveController::class,'availableParents']);
        Route::get('/{id}', [LearningObjectiveController::class, 'show']);
        Route::post('/update/{id}', [LearningObjectiveController::class, 'update']);
        Route::delete('/{id}', [LearningObjectiveController::class, 'destroy']);
        Route::post('/{id}/suggestNewQuestion',[LearningObjectiveController::class, 'generate']);

    });

    Route::prefix('zone')->middleware('auth:api')->group(function () {
        Route::get('/index', [ZoneController::class, 'index']);
        Route::post('/add', [ZoneController::class, 'store']);
        Route::get('/{id}', [ZoneController::class, 'show']);
        Route::post('/update/{id}', [ZoneController::class, 'update']);
        Route::delete('/{id}', [ZoneController::class, 'destroy']);

    });

    Route::prefix('{questionType}')->group(function () {
        Route::post('/add', [QuestionController::class, 'store']);
    });
});

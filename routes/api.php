<?php

use App\Http\Controllers\Api\AttemptController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\SchoolLevelController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
// GET /auth/login route removed – Laravel automatically returns 405 for unsupported methods
Route::post('/auth/admin-login', [AuthController::class, 'adminLogin']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::apiResource('levels', SchoolLevelController::class)
    ->parameters(['levels' => 'level'])
    ->except(['show']);

Route::apiResource('topics', TopicController::class)
    ->except(['show']);

Route::apiResource('exercises', ExerciseController::class);
Route::apiResource('courses', CourseController::class);
Route::apiResource('users', UserController::class)->only(['index']);
Route::get('topics/{topic}/progress', [ProgressController::class, 'getTopicProgress']);
Route::apiResource('badges', BadgeController::class)
    ->except(['show']);

Route::get('/attempts', [AttemptController::class, 'index']);
Route::post('/attempts', [AttemptController::class, 'store']);
Route::get('/users/{user}/progress', [ProgressController::class, 'show']);
Route::post('/upload', [App\Http\Controllers\Api\UploadController::class, 'upload']);

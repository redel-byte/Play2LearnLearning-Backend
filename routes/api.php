<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\Api\AttemptController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\UserManagementController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [ResetPasswordController::class, 'reset']);

Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::get('/attempts/my-history', [AttemptController::class, 'myHistory']);
    Route::post('/quizzes/join', [AttemptController::class, 'joinByCode']);
    Route::apiResource('quizzes', QuizController::class);
    Route::get('/quizzes/{quiz}/results', [QuizController::class, 'results']);
    Route::post('/quizzes/{quiz}/duplicate', [QuizController::class, 'duplicate'])
        ->name('quizzes.duplicate');
    Route::post('/quizzes/{quiz}/share', [QuizController::class, 'share'])
        ->name('quizzes.share');
    Route::patch('/quizzes/{quiz}/publish', [QuizController::class, 'publish'])
        ->name('quizzes.publish');
    Route::patch('/quizzes/{quiz}/unpublish', [QuizController::class, 'unpublish'])
        ->name('quizzes.unpublish');
    Route::post('/quizzes/{quiz}/attempts', [AttemptController::class, 'start']);
    Route::get('/attempts/{attempt}', [AttemptController::class, 'show']);
    Route::post('/attempts/{attempt}/answers', [AttemptController::class, 'submit']);

    Route::get('/admin/users', [UserManagementController::class, 'index']);
    Route::get('/admin/users/{user}', [UserManagementController::class, 'show']);
    Route::patch('/admin/users/{user}', [UserManagementController::class, 'update']);
});

<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InstitutionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/institution/index/', [InstitutionController::class, 'index']);
    Route::post('/institution/store', [InstitutionController::class, 'store']);
    Route::get('/institution/show/{id}', [InstitutionController::class, 'show']);
    Route::put('/institution/update/{model}', [InstitutionController::class, 'update']);
    Route::delete('/institution/destroy/{model}', [InstitutionController::class, 'destroy']);
    Route::get('/institution/list', [InstitutionController::class, 'list']);
});
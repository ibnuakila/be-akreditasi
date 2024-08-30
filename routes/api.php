<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\SubdistrictController;
use App\Http\Controllers\Api\VillageController;
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

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/province/index/', [ProvinceController::class, 'index']);
    Route::post('/province/store', [ProvinceController::class, 'store']);
    Route::get('/province/show/{id}', [ProvinceController::class, 'show']);
    Route::put('/province/update/{model}', [ProvinceController::class, 'update']);
    Route::delete('/province/destroy/{model}', [ProvinceController::class, 'destroy']);
    Route::get('/province/list', [ProvinceController::class, 'list']);
});

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/city/index/', [CityController::class, 'index']);
    Route::post('/city/store', [CityController::class, 'store']);
    Route::get('/city/show/{id}', [CityController::class, 'show']);
    Route::put('/city/update/{model}', [CityController::class, 'update']);
    Route::delete('/city/destroy/{model}', [CityController::class, 'destroy']);
    Route::get('/city/list', [CityController::class, 'list']);
});

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/subdistrict/index/', [SubdistrictController::class, 'index']);
    Route::post('/subdistrict/store', [SubdistrictController::class, 'store']);
    Route::get('/subdistrict/show/{id}', [SubdistrictController::class, 'show']);
    Route::put('/subdistrict/update/{model}', [SubdistrictController::class, 'update']);
    Route::delete('/subdistrict/destroy/{model}', [SubdistrictController::class, 'destroy']);
    Route::get('/subdistrict/list', [SubdistrictController::class, 'list']);
    Route::get('/subdistrict/cityid/{city_id}', [SubdistrictController::class, 'cityid']);
});

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/village/index/', [VillageController::class, 'index']);
    Route::post('/village/store', [VillageController::class, 'store']);
    Route::get('/village/show/{id}', [VillageController::class, 'show']);
    Route::put('/village/update/{model}', [VillageController::class, 'update']);
    Route::delete('/village/destroy/{model}', [VillageController::class, 'destroy']);
    Route::get('/village/list', [VillageController::class, 'list']);
    Route::get('/village/subdistrictid/{subdistrict_id}', [VillageController::class, 'subdistrictid']);
});

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/region/index/', [RegionController::class, 'index']);
    Route::post('/region/store', [RegionController::class, 'store']);
    Route::get('/region/show/{id}', [RegionController::class, 'show']);
    Route::put('/region/update/{model}', [RegionController::class, 'update']);
    Route::delete('/region/destroy/{model}', [RegionController::class, 'destroy']);
    Route::get('/region/list', [RegionController::class, 'list']);
});

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/proposal/index/', [ProposalController::class, 'index']);
    Route::get('/proposal/list/', [ProposalController::class, 'list']);
    Route::post('/proposal/store', [ProposalController::class, 'store']);
    Route::post('/proposal/store-files', [ProposalController::class, 'storefiles']);
    Route::get('/proposal/show/{id}', [ProposalController::class, 'show']);
    Route::put('/proposal/update/{model}', [ProposalController::class, 'update']);
    Route::delete('/proposal/destroy/{model}', [ProposalController::class, 'destroy']);
});
<?php

use App\Http\Controllers\Api\AccreditationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Api\InstrumentController;
use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\ProposalDocumentController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\SubdistrictController;
use App\Http\Controllers\Api\VillageController;
use App\Models\EvaluationAssignment;
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

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/institution/index/', [InstitutionController::class, 'index']);
    Route::post('/institution/store', [InstitutionController::class, 'store']);
    Route::get('/institution/show/{id}', [InstitutionController::class, 'show']);
    Route::put('/institution/update/{model}', [InstitutionController::class, 'update']);
    Route::delete('/institution/destroy/{model}', [InstitutionController::class, 'destroy']);
    Route::get('/institution/list', [InstitutionController::class, 'list']);
//});

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/province/index/', [ProvinceController::class, 'index']);
    Route::post('/province/store', [ProvinceController::class, 'store']);
    Route::get('/province/show/{id}', [ProvinceController::class, 'show']);
    Route::put('/province/update/{model}', [ProvinceController::class, 'update']);
    Route::delete('/province/destroy/{model}', [ProvinceController::class, 'destroy']);
    Route::get('/province/list', [ProvinceController::class, 'list']);
//});

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/city/index/', [CityController::class, 'index']);
    Route::post('/city/store', [CityController::class, 'store']);
    Route::get('/city/show/{id}', [CityController::class, 'show']);
    Route::put('/city/update/{model}', [CityController::class, 'update']);
    Route::delete('/city/destroy/{model}', [CityController::class, 'destroy']);
    Route::get('/city/list', [CityController::class, 'list']);
    Route::get('/city/getbyprovince/{province_id}', [CityController::class, 'getByProvince']);
//});

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/subdistrict/index/', [SubdistrictController::class, 'index']);
    Route::post('/subdistrict/store', [SubdistrictController::class, 'store']);
    Route::get('/subdistrict/show/{id}', [SubdistrictController::class, 'show']);
    Route::put('/subdistrict/update/{model}', [SubdistrictController::class, 'update']);
    Route::delete('/subdistrict/destroy/{model}', [SubdistrictController::class, 'destroy']);
    Route::get('/subdistrict/list', [SubdistrictController::class, 'list']);
    Route::get('/subdistrict/getbycity/{city_id}', [SubdistrictController::class, 'getByCity']);
//});

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/village/index/', [VillageController::class, 'index']);
    Route::post('/village/store', [VillageController::class, 'store']);
    Route::get('/village/show/{id}', [VillageController::class, 'show']);
    Route::put('/village/update/{model}', [VillageController::class, 'update']);
    Route::delete('/village/destroy/{model}', [VillageController::class, 'destroy']);
    Route::get('/village/list', [VillageController::class, 'list']);
    Route::get('/village/getbysubdistrict/{subdistrict_id}', [VillageController::class, 'getbysubdistrict']);
//});

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/region/index/', [RegionController::class, 'index']);
    Route::post('/region/store', [RegionController::class, 'store']);
    Route::get('/region/show/{id}', [RegionController::class, 'show']);
    Route::put('/region/update/{model}', [RegionController::class, 'update']);
    Route::delete('/region/destroy/{model}', [RegionController::class, 'destroy']);
    Route::get('/region/list', [RegionController::class, 'list']);
//});

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/proposaldocument/index/', [ProposalDocumentController::class, 'index']);
    Route::post('/proposaldocument/store', [ProposalDocumentController::class, 'store']);
    Route::get('/proposaldocument/show/{id}', [ProposalDocumentController::class, 'show']);
    Route::put('/proposaldocument/update/{model}', [ProposalDocumentController::class, 'update']);
    Route::delete('/proposaldocument/destroy/{model}', [ProposalDocumentController::class, 'destroy']);
    Route::get('/proposaldocument/list', [ProposalDocumentController::class, 'list']);
//});

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/proposal/index/', [ProposalController::class, 'index']);
    Route::get('/proposal/list/', [ProposalController::class, 'list']);
    Route::post('/proposal/store', [ProposalController::class, 'store']);
    Route::post('/proposal/store-files', [ProposalController::class, 'storefiles']);
    Route::get('/proposal/show/{id}', [ProposalController::class, 'show']);
    Route::put('/proposal/update/{model}', [ProposalController::class, 'update']);
    Route::delete('/proposal/destroy/{model}', [ProposalController::class, 'destroy']);
//});

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/instrument/get-instrument/{params}', [InstrumentController::class, 'getInstrument']);
    Route::post('/instrument/generate-proposal-document/', [InstrumentController::class, 'generateProposalDocument']);
//});

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/evaluationassignment/index/', [EvaluationAssignment::class, 'index']);
    Route::get('/evaluationassignment/list/', [EvaluationAssignment::class, 'list']);
    Route::get('/evaluationassignment/show/{id}', [EvaluationAssignment::class, 'show']);
    Route::post('/evaluationassignment/store/', [EvaluationAssignment::class, 'store']);
    Route::post('/evaluationassignment/update/', [EvaluationAssignment::class, 'update']);
    Route::delete('/evaluationassignment/destroy/{id}', [EvaluationAssignment::class, 'destroy']);
//});

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/accreditation/index/{user_id}', [AccreditationController::class, 'index']);
    Route::post('/accreditation/store', [AccreditationController::class, 'store']);
    Route::get('/accreditation/show/{id}', [AccreditationController::class, 'show']);
    Route::put('/accreditation/update/{model}', [AccreditationController::class, 'update']);
    Route::delete('/accreditation/destroy/{model}', [AccreditationController::class, 'destroy']);
    Route::get('/accreditation/list', [AccreditationController::class, 'list']);
    Route::get('/accreditation/addnew/{user_id}', [AccreditationController::class, 'addNew']);
    Route::post('/accreditation/store-files', [AccreditationController::class, 'storeFiles']);
    Route::get('/accreditation/edit/{user_id}', [AccreditationController::class, 'addNew']);
//});
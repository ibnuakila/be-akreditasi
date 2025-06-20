<?php

use App\Http\Controllers\Api\AccreditationController;
use App\Http\Controllers\Api\AssessorController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\EvaluationAssignmentController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Api\InstrumentAspectController;
use App\Http\Controllers\Api\InstrumentComponentController;
use App\Http\Controllers\Api\InstrumentController;
use App\Http\Controllers\Api\ProposalAssignmentController;
use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\ProposalDocumentController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\SubdistrictController;
use App\Http\Controllers\Api\VillageController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AccreditationFeedbackController;
use App\Http\Controllers\Api\EvaluationSurveilanceController;
use App\Http\Controllers\Api\SurveilanceAssignmentController;
use App\Models\EvaluationAssignment;
use App\Models\EvaluationSurveilance;
use App\Models\InstrumentComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/info', [AuthController::class,'info']);

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
    Route::post('/proposal/update/{id}', [ProposalController::class, 'update']);
    Route::delete('/proposal/destroy/{model}', [ProposalController::class, 'destroy']);
    Route::get('/proposal/show-file/{id}/{file}', [ProposalController::class, 'showFile']);
    Route::get('/proposal/show-file/{id}', [ProposalController::class, 'showFiles']);
//});

//Route::middleware('auth:sanctum')->group(function(){
    Route::post('/instrument/add-new/', [InstrumentController::class, 'addnew']);
    Route::get('/instrument/get-instrument/{params}', [InstrumentController::class, 'getInstrument']);
    Route::get('/instrument/edit/{id}', [InstrumentController::class, 'edit']);
    Route::get('/instrument/index', [InstrumentController::class, 'index']);
    Route::post('/instrument/store', [InstrumentController::class, 'store']);
    Route::post('/instrument/update/{id}', [InstrumentController::class, 'update']);
    Route::delete('/instrument/destroy/{model}', [InstrumentController::class, 'destroy']);
    Route::get('/instrument/get-document-sk/{id}', [InstrumentController::class, 'getDocumentSK']);
    //});

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/accreditation/index/{user_id}', [AccreditationController::class, 'index']);
    Route::post('/accreditation/store', [AccreditationController::class, 'store']);
    Route::get('/accreditation/show/{id}', [AccreditationController::class, 'show']);
    Route::post('/accreditation/update/{id}', [AccreditationController::class, 'update']);
    Route::delete('/accreditation/destroy/{id}', [AccreditationController::class, 'destroy']);
    Route::get('/accreditation/list', [AccreditationController::class, 'list']);
    Route::get('/accreditation/addnew/{user_id}', [AccreditationController::class, 'addNew']);
    Route::post('/accreditation/store-files', [AccreditationController::class, 'storeFiles']);
    Route::get('/accreditation/edit/{id}', [AccreditationController::class, 'edit']);
    Route::delete('/accreditation/destroy-file/{id}', [AccreditationController::class, 'destroyFile']);
    Route::get('/accreditation/show-file/{id}', [AccreditationController::class, 'showFile']);
//});

    Route::get('/instrument-aspect/list/{instrument_id}', [InstrumentAspectController::class, 'list']);
    Route::get('/instrument-aspect/add-new/{instrument_id}', [InstrumentAspectController::class, 'addNew']);
    Route::post('/instrument-aspect/store/', [InstrumentAspectController::class, 'store']);
    Route::delete('/instument-aspect/destroy/{id}', [InstrumentAspectController::class, 'destroy']);
    Route::post('/instrument-aspect/update/{id}', [InstrumentAspectController::class, 'update']);
    Route::get('/instrument-aspect/show/{id}', [InstrumentAspectController::class, 'show']);

    Route::get('/instrument-component/add-new/{instrument_id}', [InstrumentComponentController::class, 'addNew']);
    Route::get('/instrument-component/get-component/{parent_id}/{type}', [InstrumentComponentController::class, 'getComponent']);
    Route::get('/instrument-component/get-main-component/{instrument_id}', [InstrumentComponentController::class, 'getMainComponent']);
    Route::post('/instrument-component/store', [InstrumentComponentController::class, 'store']);
    Route::delete('/instrument-component/destroy/{id}', [InstrumentComponentController::class, 'destroy']);
    Route::post('/instrument-component/update/{id}', [InstrumentComponentController::class, 'update']);


    Route::get('/assessor/index', [AssessorController::class, 'index']);
    Route::get('/assessor/show/{id}', [AssessorController::class, 'show']);
    Route::delete('/assessor/destroy/{model}', [AssessorController::class, 'destroy']);
    Route::post('/assessor/update/{model}', [AssessorController::class, 'update']);
    Route::get('/assessor/edit/{id}', [AssessorController::class, 'edit']);
    Route::post('/assessor/store/', [AssessorController::class, 'store']);

    Route::get('/proposal-assignment/index', [ProposalAssignmentController::class, 'index']);
    Route::get('/proposal-assignment/list', [ProposalAssignmentController::class, 'list']);
    Route::get('/proposal-assignment/add-new/{id}', [ProposalAssignmentController::class, 'addNew']);
    Route::post('/proposal-assignment/store/', [ProposalAssignmentController::class, 'store']);
    Route::delete('/proposal-assignment/destroy/{id}', [ProposalAssignmentController::class, 'destroy']);
    Route::get('/proposal-assignment/edit/{id}', [ProposalAssignmentController::class, 'edit']);
    Route::post('/proposal-assignment/update/{id}', [ProposalAssignmentController::class, 'update']);

//Route::middleware('auth:sanctum')->group(function(){
    Route::get('/evaluation-assignment/addnew/{id}', [EvaluationAssignmentController::class, 'addNew']);
    Route::get('/evaluation-assignment/index/', [EvaluationAssignmentController::class, 'index']);
    Route::get('/evaluation-assignment/list/', [EvaluationAssignmentController::class, 'list']);
    Route::get('/evaluation-assignment/show/{id}', [EvaluationAssignmentController::class, 'show']);
    Route::post('/evaluation-assignment/store/', [EvaluationAssignmentController::class, 'store']);
    Route::post('/evaluation-assignment/update/', [EvaluationAssignmentController::class, 'update']);
    Route::delete('/evaluation-assignment/destroy/{id}', [EvaluationAssignmentController::class, 'destroy']);
    Route::post('/evaluation-assignment/upload-instrument/', [EvaluationAssignmentController::class, 'uploadInstrument']);
//});
    
Route::get('/evaluation/list', [EvaluationController::class,'list']);
Route::get('/evaluation/show/{id}', [EvaluationController::class,'show']);
Route::post('/evaluation/store', [EvaluationController::class,'store']);
Route::post('/evaluation/update/{id}', [EvaluationController::class, 'update']);
Route::post('/evaluation/update-row/', [EvaluationController::class, 'updateRow']);

Route::get('/accreditation-feedback/addnew/{accreditation_proposal_id}', [AccreditationFeedbackController::class, 'addNew']);
Route::get('/accreditation-feedback/show/{id}', [AccreditationFeedbackController::class, 'show']);
Route::get('/accreditation-feedback/index', [AccreditationFeedbackController::class, 'index']);
Route::post('/accreditation-feedback/store', [AccreditationFeedbackController::class, 'store']);
Route::delete('/accreditation-feedback/destroy/{model}', [AccreditationFeedbackController::class, 'destroy']);
Route::post('/accreditation-feedback/update/{model}', [AccreditationFeedbackController::class, 'update']);
Route::get('/accreditation-feedback/export', [AccreditationFeedbackController::class, 'export']);
Route::get('/accreditation/getperpustakaan', [AccreditationController::class, 'getPerpustakaan']);

Route::get('/evaluation-surveilance/addnew/{id}', [EvaluationSurveilanceController::class, 'addNew']);
Route::post('/evaluation-surveilance/store', [EvaluationSurveilanceController::class, 'store']);
Route::get('/evaluation-surveilance/list', [EvaluationSurveilanceController::class, 'list']);
Route::post('/evaluation-surveilance/update/{model}', [EvaluationSurveilanceController::class, 'update']);
Route::get('/evaluation-surveilance/show/{id}', [EvaluationSurveilanceController::class, 'show']);

Route::get('/surveilance-assignment/list', [SurveilanceAssignmentController::class, 'list']);

Route::get('/dashboard/index', [DashboardController::class, 'index']);
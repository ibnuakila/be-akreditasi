<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\TestInstrumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-instrument/index/{category}', [TestInstrumentController::class,'index']);

Route::get('/test-instrument/get-detail-instrument/{category}', [TestInstrumentController::class, 'getDetailInstrument']);
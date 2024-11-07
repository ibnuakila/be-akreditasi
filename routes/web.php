<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\TestInstrumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-instrument/index/{category}', [TestInstrumentController::class,'index']);


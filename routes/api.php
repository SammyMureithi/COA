<?php

use App\Http\Controllers\PdfQuestioneerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'v1'], function () {
    //Pdf Routes
    Route::group(['prefix' => 'test'], function () {
        Route::post('/upload', [PdfQuestioneerController::class, 'readAndFillQuestioneers']);
        Route::get('/', [PdfQuestioneerController::class, 'getTestDetails']);
    });

    Route::group(['prefix' => 'excell'], function () {
        Route::post('/upload', [PdfQuestioneerController::class, 'uploadExcell']);
        Route::get('/', [PdfQuestioneerController::class, 'getExports']);
    });
});
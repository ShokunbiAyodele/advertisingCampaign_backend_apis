<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CampaignController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/createcampaign', [CampaignController::class ,'StoreCampaign']);

Route::get('/getcampaign', [CampaignController::class ,'ReadCampaign']);
Route::get('/getcampaignsInfo', [CampaignController::class ,'ReadCampaigns']);


Route::get('/getcampaign/{id}', [CampaignController::class ,'EditCampaign']);

Route::post('/updatecampaign/{id}',[CampaignController::class ,'updateCampaign']);

Route::get('/loadimages/{id}', [CampaignController::class ,'Previewimage']);



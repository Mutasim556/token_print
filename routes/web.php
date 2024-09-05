<?php

use App\Http\Controllers\PrintController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::controller(PrintController::class)->group(function(){
    Route::get('/print','index');
    Route::get('/test-print','testPrint');
    Route::post('/custom-print','testPrint')->name('customPrint');
    Route::get('/getdata','getData');
    // Route::get('/print-config','config');
    Route::post('/print-config','configUpdate')->name('print_config');
    Route::get('/get-current-meal-info','getCurrentMealInfo');
    Route::get('/start-schedule','startSchedule')->name('startSchedule');
});

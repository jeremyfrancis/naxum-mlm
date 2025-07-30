<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommissionReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Commission Report route
Route::get('/commission-report', [CommissionReportController::class, 'index'])->name('commission-report');

// Test route
Route::get('/test', function() {
    return "Route is working!";
}); 
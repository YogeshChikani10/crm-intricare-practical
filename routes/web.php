<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

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
    return redirect('/contact');
});

// Contact modules route.
Route::prefix('contact')->group(function () { 
    Route::get('/', [ContactController::class, 'contact'])->name('contact');
    Route::post('/save', [ContactController::class, 'save'])->name('contact-save');
    Route::post('/list', [ContactController::class, 'list'])->name('contact-list');
    Route::post('/details', [ContactController::class, 'details'])->name('contact-details');
    Route::post('/delete', [ContactController::class, 'delete'])->name('contact-delete');
    Route::post('/merge', [ContactController::class, 'mergeContact'])->name('contact-merge');
    Route::post('/list/primary', [ContactController::class, 'primaryContactList'])->name('primary-contact-list');
} );

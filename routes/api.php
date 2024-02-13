<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookAcionController;
use App\Http\Controllers\BookTransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'registerUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/login', [AuthController::class, 'loginUser']);
    Route::get('/fetch-user/{user_id?}', [AuthController::class, 'getUsers']);
    Route::delete('/delete-user/{user_id}', [AuthController::class, 'getUsers']);
});
Route::prefix('books')->group(function () {
    Route::get('get/{book_id?}', [BookAcionController::class, 'getBooks']);
    Route::post('post', [BookAcionController::class, 'postBooks']);
    Route::put('update/{book_id}', [BookAcionController::class, 'updateBook']);
    Route::delete('delete/{book_id}', [BookAcionController::class, 'deleteBook']);
});
Route::prefix('lent')->group(function () {
    Route::get('get/{transaction_id?}', [BookTransactionController::class, 'getBookTrasactions']);
    Route::post('post/{book_id}', [BookTransactionController::class, 'lendBooks']);
    Route::put('update', [BookTransactionController::class, 'updateTransa']);
    Route::post('post', [BookTransactionController::class, 'lendBooks']);
});
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\api\v1\UserController;
use \App\Http\Controllers\api\v1\ChannelController;
use \App\Http\Controllers\api\v1\QuestController;
use \App\Http\Controllers\api\v1\TagController;
use \App\Http\Controllers\api\v1\AnswerController;
use \App\Http\Controllers\api\v1\AuthenticationController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* Channels Routes */
Route::controller(ChannelController::class)->prefix('channels')->group(function () {
    Route::get('/', 'index')->name('channel.index');
    Route::get('/{channelUuid}', 'show')->name('channel.show');
    Route::post('/', 'store')->name('channel.store');
    Route::patch('/{channelUuid}', 'update')->name('channel.update');
    Route::delete('/{channelUuid}', 'delete')->name('channel.delete');
    Route::delete('/{channelUuid}/destroy', 'destroy')->name('channel.destroy');
    Route::get('/{channelUuid}/restore', 'restore')->name('channel.restore');
    Route::get('/clean', 'clean')->name('channel.clean');
});

/* Users Routes */
Route::controller(UserController::class)->prefix('users')->group(function () {
    Route::get('/', 'index')->name('users.index');
    Route::get('/{userUuid}', 'show')->name('users.show');
    Route::post('/', 'store')->name('users.store');
    Route::patch('/{userUuid}', 'update')->name('users.update');
    Route::delete('/{userUuid}', 'delete')->name('users.delete');
    Route::delete('/{userUuid}', 'destroy')->name('users.destroy');
    Route::get('/{uuid}/restore', 'restore')->name('users.restore');
    Route::get('/clean', 'clean')->name('users.clean');
});

/* Users Routes */
Route::controller(QuestController::class)->prefix('quests')->group(function () {
    Route::get('/', 'index')->name('quests.index');
    Route::get('/{questUuid}', 'show')->name('quests.show');
    Route::post('/', 'store')->name('quests.store');
    Route::patch('/{questUuid}', 'update')->name('quests.update');
    Route::delete('/{questUuid}', 'delete')->name('quests.delete');
    Route::delete('/{questUuid}', 'destroy')->name('quests.destroy');
    Route::get('/{questUuid}/restore', 'restore')->name('quests.restore');
    Route::get('/clean', 'clean')->name('quests.clean');
});

Route::get('/test',function (){
   dd(\App\Models\Quest::first()->tags->pluck('name'));
});

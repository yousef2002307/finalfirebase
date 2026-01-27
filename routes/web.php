<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FirebaseController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/notification/send/{userId}', [NotificationController::class, 'sendNotification']);
Route::get('/notification/show/{userId}', [NotificationController::class, 'showNotifications']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/firebase-test', [FirebaseController::class, 'test']);

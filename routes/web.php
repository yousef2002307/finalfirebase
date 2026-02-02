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


Route::get('notification', [FirebaseController::class, 'notificationPage'])->name('notification');
Route::post('save-token', [FirebaseController::class, 'saveToken'])->name('save.token');
Route::post('save-token/{userId}', [FirebaseController::class, 'saveTokenByUserId'])->name('save.token.user');



Route::get('/send-notification', [FirebaseController::class, 'showSendForm'])->name('notification.form');
Route::post('/send-notification', [FirebaseController::class, 'sendNotification'])->name('send.notification');

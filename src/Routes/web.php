<?php

use Illuminate\Support\Facades\Route;
use Webkul\Google\Http\Controllers\AccountController;
use Webkul\Google\Http\Controllers\CalendarController;
use Webkul\Google\Http\Controllers\GmailController;
use Webkul\Google\Http\Controllers\MeetController;
use Webkul\Google\Http\Controllers\WebhookController;

Route::group([
    'prefix'     => 'admin/google',
    'middleware' => ['web'],
], function () {
    Route::group(['middleware' => ['user']], function () {
        Route::controller(AccountController::class)->group(function () {
            Route::get('', 'index')->name('admin.google.index');

            Route::get('oauth', 'store')->name('admin.google.store');

            Route::delete('{id}', 'destroy')->name('admin.google.destroy');
        });

        Route::post('sync/{id}', [CalendarController::class, 'sync'])->name('admin.google.calendar.sync');

        Route::post('create-link', [MeetController::class, 'createLink'])->name('admin.google.meet.create_link');

        // Gmail Routes
        Route::group(['prefix' => 'gmail'], function () {
            Route::controller(GmailController::class)->group(function () {
                Route::get('/', 'index')->name('admin.google.gmail.index');
                Route::get('/compose', 'compose')->name('admin.google.gmail.compose');
                Route::post('/compose', 'compose')->name('admin.google.gmail.send');
                Route::get('/sync', 'sync')->name('admin.google.gmail.sync');
                Route::get('/{messageId}', 'show')->name('admin.google.gmail.show');
                Route::get('/{messageId}/reply', 'reply')->name('admin.google.gmail.reply');
                Route::post('/{messageId}/reply', 'reply')->name('admin.google.gmail.send_reply');
                Route::delete('/{messageId}', 'delete')->name('admin.google.gmail.delete');
            });
        });
    });

    Route::post('webhook', [WebhookController::class])->name('admin.google.webhook');
});

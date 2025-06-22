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

        // Gmail Configuration Routes
        Route::group(['prefix' => 'gmail'], function () {
            Route::controller(GmailController::class)->group(function () {
                Route::get('/', 'index')->name('admin.google.gmail.index');
                Route::post('/{accountId}/enable', 'enable')->name('admin.google.gmail.enable');
                Route::post('/{accountId}/disable', 'disable')->name('admin.google.gmail.disable');
                Route::post('/{accountId}/test', 'test')->name('admin.google.gmail.test');
            });
        });
    });

    Route::post('webhook', [WebhookController::class])->name('admin.google.webhook');
});

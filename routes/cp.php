<?php

use Illuminate\Support\Facades\Route;
use GertTimmerman\StatamicZapier\Http\Controllers\WebhooksController;

Route::prefix('statamic-zapier')->name('statamic-zapier.')->group(function () {
    Route::get('/', [WebhooksController::class, 'edit'])->name('index');
    Route::post('/', [WebhooksController::class, 'update'])->name('update');
});
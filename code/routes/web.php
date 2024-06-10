<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});

Route::get('app/deposit/confirm/{hash}/{coinPlanId}', 'Gateway\PaymentController@appDepositConfirm')->name('deposit.app.confirm');

Route::controller('SiteController')->group(function(){
    Route::get('/', 'index')->name('home');
    Route::get('placeholder-image/{size}', 'placeholderImage')->name('placeholder.image');
});

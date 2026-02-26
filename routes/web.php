<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});



Route::get('/test-email', function () {

    Mail::raw('This is a test email from FCPS mail configuration.', function ($message) {
        $message->to('khizri743@gmail.com')
                ->subject('FCPS Test Email');
    });

    return 'Test email sent successfully!';
});

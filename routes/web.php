<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/proxy-test', function (Request $request) {
    return response()->json([
        'url' => $request->fullUrl(),
        'scheme' => $request->getScheme(),
        'secure' => $request->isSecure(),
        'host' => $request->getHost(),
        'x_forwarded_proto' => $request->header('X-Forwarded-Proto'),
        'x_forwarded_host' => $request->header('X-Forwarded-Host'),
        'x_forwarded_for' => $request->header('X-Forwarded-For'),
    ]);
});

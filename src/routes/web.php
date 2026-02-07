<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Kubernetes liveness probe - process is alive
Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});

// Kubernetes readiness probe - dependencies are reachable
Route::get('/ready', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'ready'], 200);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'not ready', 'error' => $e->getMessage()], 503);
    }
});

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/admin/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/admin/login');
});

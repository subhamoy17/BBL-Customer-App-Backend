<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Live Server cache, route, view clear
//Clear Cache facade value:
Route::get('/cache-clear', function() {
$exitCode = Artisan::call('cache:clear');
return '<h1>Cache facade value cleared</h1>';
});

//Reoptimized class loader:
Route::get('/optimize', function() {
$exitCode = Artisan::call('optimize');
return '<h1>Reoptimized class loader</h1>';
});

//Clear Route cache:
Route::get('/route-clear', function() {
$exitCode = Artisan::call('route:clear');
return '<h1>Route cache cleared</h1>';
});

//Clear View cache:
Route::get('/view-clear', function() {
$exitCode = Artisan::call('view:clear');
return '<h1>View cache cleared</h1>';
});

//Clear Config cache:
Route::get('/config-cache', function() {
$exitCode = Artisan::call('config:cache');
return '<h1>Clear Config cleared</h1>';
});
//Clear Config cache:
Route::get('/queue-worker', function() {
$exitCode = Artisan::call('queue:work');
return '<h1>Started Queue Worker</h1>';
});
Route::get('/queue-flush', function() {
$exitCode = Artisan::call('queue:flush');
return '<h1>Started Queue Worker</h1>';
});
Route::get('info', function () {
return phpversion();
});
Route::get('passport', function() {
$exitCode = Artisan::call('passport:install');
return '<h1>Passport installed</h1>';
});
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartementController;
use App\Http\Controllers\Api\JabatanController;
use App\Http\Controllers\Api\KpiCategoriesController;
use App\Http\Controllers\Api\KpiIndicatorController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});




Route::middleware(['auth:api'])->group(function () {

    Route::prefix('users')->controller(AuthController::class)->group(function () {
        Route::get('/', 'index');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

    Route::apiResources([
        'roles'            => RoleController::class,
        'permissions'      => PermissionController::class,
        'departement'      => DepartementController::class,
        'jabatan'          => JabatanController::class,
        'kpi-category'     => KpiCategoriesController::class,
        'kpi-indicator'    => KpiIndicatorController::class,
    ]);
});

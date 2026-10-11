<?php

use Vergatan10\Installer\Http\Controllers\InstallerController;
use Vergatan10\Installer\Http\Middleware\EnsureNotInstalled;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', EnsureNotInstalled::class])
    ->prefix(config('installer.route_prefix', 'install'))
    ->name('installer.')
    ->controller(InstallerController::class)
    ->group(function () {
        Route::get('/', 'welcome')->name('welcome');
        Route::get('requirements', 'requirements')->name('requirements');
        Route::get('permissions', 'permissions')->name('permissions');
        Route::get('database', 'database')->name('database');
        Route::post('database', 'saveDatabase')->name('database.save');
        Route::post('database/test', 'testDatabase')->name('database.test');
        Route::get('admin', 'admin')->name('admin');
        Route::post('admin', 'saveAdmin')->name('admin.save');
        Route::get('run', 'run')->name('run');
        Route::post('run/{task}', 'runTask')->name('run.task');
    });

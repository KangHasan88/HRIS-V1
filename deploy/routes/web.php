<?php

use App\Http\Controllers\HrisController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HrisController::class, 'index'])->name('hris.dashboard');
Route::get('/ess', [HrisController::class, 'ess'])->name('hris.ess');
Route::get('/employees', [HrisController::class, 'employees'])->name('hris.employees');
Route::get('/attendance', [HrisController::class, 'attendance'])->name('hris.attendance');
Route::get('/leave', [HrisController::class, 'leave'])->name('hris.leave');
Route::get('/payroll', [HrisController::class, 'payroll'])->name('hris.payroll');
Route::get('/documents', [HrisController::class, 'documents'])->name('hris.documents');

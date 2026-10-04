<?php

use App\Http\Controllers\LocalAttendanceAgentController;
use Illuminate\Support\Facades\Route;

Route::post('/attendance-agent/clock-events', [LocalAttendanceAgentController::class, 'ingest'])
    ->name('api.attendance-agent.clock-events');

Route::post('/attendance-agent/heartbeat', [LocalAttendanceAgentController::class, 'heartbeat'])
    ->name('api.attendance-agent.heartbeat');

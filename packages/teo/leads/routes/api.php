<?php

use Illuminate\Support\Facades\Route;
use Teo\Leads\Http\Controllers\LeadController;

Route::post('/api/leads', [LeadController::class, 'store']);

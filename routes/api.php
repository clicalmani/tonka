<?php

use Clicalmani\Foundation\Support\Facades\Route;

/**
 * |-------------------------------------------------------------------------------
 * | Unauthenticated Routes
 * |-------------------------------------------------------------------------------
 * 
 * Routes without authentication should go here before the middleware.
 * 
 */

//

/**
 * |-------------------------------------------------------------------------------
 * | Authenticated Routes
 * |-------------------------------------------------------------------------------
 * 
 * Based on JWT token
 */
Route::middleware('tokenizer');
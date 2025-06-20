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
 * 
 * No token will be saved to the database. The end user will be liable to store the token anywhere he wants
 * and provide it for each request. The token signature will be checked on the backend to verify its validity.
 * By default a token life duration is set to 1 day (24 hours), this behavior can be change and set to whatever you want.
 * 
 */
Route::middleware('tokenizer');
<?php

use Clicalmani\Foundation\Support\Facades\Route;

/**
 * |-------------------------------------------------------------------------------
 * | Web Routes
 * |-------------------------------------------------------------------------------
 * 
 */

Route::get('/', function () {
    return view('welcome');
});
<?php

// Internal Api calls
Route::get('airHackApiHealth', 'ApiController@airHackApiHealth');
Route::get('tasks', 'ApiController@tasks');

// External Api calls (called by AirHack Api)
Route::get('hooks/health', 'HookController@health');
Route::post('hooks/incomingTasks', 'HookController@incomingTasks');
Route::get('hooks/getData', 'GetDataController@getData');
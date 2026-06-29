<?php

use Illuminate\Support\Facades\Route;
use App\Models\Student;

Route::get('/', function () {
    return 'Laravel Connected';
});

Route::get('/students', function () {
    return Student::all();
});
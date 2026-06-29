<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentChangeLog extends Model
{
    protected $table = 'student_change_logs';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'changed_field',
        'old_value',
        'new_value'
    ];
}
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'students';

    protected $primaryKey = 'id';

    public $timestamps = true;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'dob',
        'address',
        'city',
        'state',
        'country',
        'course',
        'profile_image',
        'password',
        'is_active',
    ];

    /**
     * Hidden fields in API responses
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'dob' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

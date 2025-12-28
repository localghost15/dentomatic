<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'fullname',
        'phone',
        'telegram',
        'doctor',
        'datetime',
        'source',
        'lang_pref'
    ];

    protected $casts = [
        'datetime' => 'datetime',
    ];
}

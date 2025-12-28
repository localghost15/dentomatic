<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'specialization',
        'phone',
        'calendar_color',
        'photo_path',
        'status',
        'telegram_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

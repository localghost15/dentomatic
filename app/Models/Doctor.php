<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'full_name',
        'specialization',
        'phone',
        'calendar_color',
        'photo_path',
        'status',
        'telegram_id',
        'telegram_chat_id',
        'tg_auth_code'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

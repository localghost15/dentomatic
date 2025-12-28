<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type'
    ];

    // Accessor to decrypt value automatically if needed
    // However, for API we might want to mask it instead of returning decrypted value directly
    // Let's handle decryption explicitly in Helpers or Controllers when needed for backend logic
    // For frontend, we return the value as is (which will be encrypted if it was stored encrypted)
    // Actually, user requirement: "Bot Token... stored encrypted... validation... masked version".
    
    // We'll keep it simple: Controller handles logic.
}

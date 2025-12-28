<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        // Cache settings to avoid DB queries on every call
        $setting = Cache::rememberForever("setting.{$key}", function () use ($key) {
            return Setting::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        $value = $setting->value;

        // Decrypt if necessary (we assume all "secret" type keys are encrypted or we handle logic here)
        // For simplicity, let's say if the key contains 'token' or 'secret', we try to decrypt
        // Or better, we trust the Setting model accessor if implemented, but here we do it broadly.
        // Actually, let's implement the logic in the Model Accessor for cleaner code,
        // so this helper just returns the value.
        
        return $value;
    }
}

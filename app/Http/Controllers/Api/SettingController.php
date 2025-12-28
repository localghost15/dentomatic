<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SettingController extends Controller
{
    public function index()
    {
        // Return settings but mask secrets
        $settings = Setting::all()->map(function ($setting) {
            if ($setting->key === 'telegram_bot_token' && $setting->value) {
                $setting->value = '********'; // Mask secret
            }
            return $setting;
        });
        
        return $settings->pluck('value', 'key');
    }

    public function update(Request $request)
    {
        // Expects { key: value, key2: value2 }
        $data = $request->all();

        foreach ($data as $key => $value) {
            if ($key === 'telegram_bot_token') {
                if ($value === '********' || empty($value)) {
                    continue; // Skip if masked or empty
                }
                $value = Crypt::encryptString($value);
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['message' => 'Settings saved']);
    }
}

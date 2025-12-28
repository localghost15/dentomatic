<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $apiUrl = 'https://api.telegram.org/bot';

    public function __construct()
    {
        $this->botToken = setting('telegram_bot_token');
    }

    /**
     * Send a message to a specific chat ID.
     *
     * @param string $chatId
     * @param string $text
     * @return bool
     */
    public function sendMessage($chatId, $text)
    {
        if (!$this->botToken) {
            Log::warning('Telegram bot token is not set.');
            return false;
        }

        $url = $this->apiUrl . $this->botToken . '/sendMessage';
        
        // SSL Bypass logic for local environment
        $verify = true;
        if (app()->environment('local')) {
            $verify = false;
        }

        try {
            $response = Http::withOptions([
                'verify' => $verify,
            ])->post($url, [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                Log::info("Telegram message sent to {$chatId}");
                return true;
            } else {
                Log::error("Failed to send Telegram message: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Telegram Service Error: " . $e->getMessage());
            return false;
        }
    }
}

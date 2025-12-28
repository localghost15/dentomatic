<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram Webhook:', $update);

        $message = $update['message'] ?? null;
        if (!$message) {
            return response()->json(['status' => 'ok']); // Acknowledge generic updates
        }

        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');

        if (!$chatId || !$text) {
            return response()->json(['status' => 'ignored']);
        }

        // Check if text matches any tg_auth_code in database
        $doctor = Doctor::where('tg_auth_code', $text)->first();

        if ($doctor) {
            // Bind the doctor
            $doctor->update([
                'telegram_chat_id' => $chatId,
                'tg_auth_code' => null // Clear code after usage
            ]);

            // Send success message
            $msg = "Поздравляем, <b>{$doctor->full_name}</b>!\n" .
                   "Ваш аккаунт успешно привязан к системе Dentomatic.\n" .
                   "Теперь вы будете получать уведомления о новых записях.";
            
            $this->telegramService->sendMessage($chatId, $msg);
            
            Log::info("Doctor {$doctor->id} bound to Chat ID {$chatId}");
        } else {
             // Optional: Reply only if it looks like a code (digits) to avoid spamming normal chat?
             // User asked: "If code incorrect — answer: Code not found..."
             // Let's reply.
             $this->telegramService->sendMessage($chatId, "Код не найден. Пожалуйста, проверьте код в системе.");
        }

        return response()->json(['status' => 'handled']);
    }
}

<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\Doctor;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking)
    {
        if (!$booking->doctor) {
            return;
        }

        // booking->doctor might be string (old) or int (new ID).
        // Try to find doctor by ID.
        $doctor = null;
        if (is_numeric($booking->doctor)) {
             $doctor = Doctor::find($booking->doctor);
        }

        if ($doctor && $doctor->telegram_chat_id) {
            $lang = $booking->lang_pref ?? 'ru';
            $message = $this->getNotificationMessage($lang, $booking);
            
            $this->telegramService->sendMessage($doctor->telegram_chat_id, $message);
        }
    }

    protected function getNotificationMessage($lang, $booking)
    {
        // Simple localization logic
        if ($lang === 'uz') {
            return "🆕 <b>Yangi qabul!</b>\n\n" .
                   "👤 Bemor: <b>{$booking->fullname}</b>\n" .
                   "📞 Telefon: {$booking->phone}\n" .
                   "📅 Vaqt: {$booking->datetime->format('d.m.Y H:i')}\n" .
                   "ℹ️ Manba: {$booking->source}";
        } else {
            // Default RU
             return "🆕 <b>Новая запись!</b>\n\n" .
                   "👤 Пациент: <b>{$booking->fullname}</b>\n" .
                   "📞 Телефон: {$booking->phone}\n" .
                   "📅 Время: {$booking->datetime->format('d.m.Y H:i')}\n" .
                   "ℹ️ Источник: {$booking->source}";
        }
    }
}

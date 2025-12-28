<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings (formatted for FullCalendar).
     */
    public function index(Request $request)
    {
        try {
            $start = $request->query('start');
            $end = $request->query('end');

            $query = Booking::query();

            if ($start && $end) {
                // Carbon parse with explicit timezone handling
                // Example input: 2025-12-28T00:00:00+05:00
                $startDate = \Carbon\Carbon::parse($start);
                $endDate = \Carbon\Carbon::parse($end);
                
                $query->whereBetween('datetime', [$startDate, $endDate]);
            }

            $bookings = $query->get();

            return $bookings->map(function ($booking) {
                $color = '#7367f0'; 
                if ($booking->doctor === 'dr_smith') $color = '#ff9f43';
                if ($booking->doctor === 'dr_doe') $color = '#28c76f'; 
                
                return [
                    'id' => $booking->id,
                    'title' => $booking->fullname . ' (' . $booking->phone . ')',
                    'start' => $booking->datetime->toIso8601String(),
                    'color' => $color,
                    'extendedProps' => [
                         'fullname' => $booking->fullname,
                         'phone' => $booking->phone,
                         'telegram' => $booking->telegram,
                         'doctor' => $booking->doctor,
                         'source' => $booking->source,
                         'lang_pref' => $booking->lang_pref
                    ]
                ];
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Booking Fetch Error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created booking in storage.
     */
    /**
     * Store a newly created booking in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'telegram' => 'nullable|string|max:50',
            'doctor' => 'required|string',
            'datetime' => 'required|date',
            'source' => 'required|string',
            'lang_pref' => 'required|string|max:10',
        ]);

        $booking = Booking::create($validated);

        return response()->json($booking, 201);
    }

    /**
     * Update the specified booking in storage.
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'telegram' => 'nullable|string|max:50',
            'doctor' => 'required|string',
            'datetime' => 'required|date',
            'source' => 'required|string',
            'lang_pref' => 'required|string|max:10',
        ]);

        $booking->update($validated);

        return response()->json($booking);
    }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return response()->json(['message' => 'Booking deleted']);
    }
}

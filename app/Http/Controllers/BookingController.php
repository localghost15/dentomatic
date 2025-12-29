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

            $query = Booking::with(['doctorInfo' => function ($q) {
                $q->withTrashed();
            }]);

            if ($start && $end) {
                // Carbon parse with explicit timezone handling
                // Example input: 2025-12-28T00:00:00+05:00
                $startDate = \Carbon\Carbon::parse($start);
                $endDate = \Carbon\Carbon::parse($end);
                
                $query->whereBetween('datetime', [$startDate, $endDate]);
            }

            $bookings = $query->get();

            return $bookings->map(function ($booking) {
                $color = '#7367f0'; // Default
                $isArchived = false;
                $doctorName = 'Unknown';
                
                // Get raw doctor attribute (ID or String)
                $rawDoctor = $booking->getAttribute('doctor');

                // Handle relation if exists
                if ($booking->doctorInfo) {
                    $doctorName = $booking->doctorInfo->full_name;
                    $color = $booking->doctorInfo->calendar_color;
                    
                    if ($booking->doctorInfo->trashed()) {
                        $isArchived = true;
                        $color = '#b0b0b0'; // Gray for archived
                    }
                } 
                // Legacy system fallback (if doctor is still a string in DB but no relation found)
                else {
                    // Try to infer color/status if needed, or leave defaults
                    if ($rawDoctor === 'dr_smith') $color = '#ff9f43';
                    if ($rawDoctor === 'dr_doe') $color = '#28c76f';
                }
                
                $title = $booking->fullname . ' (' . $booking->phone . ')';
                
                return [
                    'id' => $booking->id,
                    'title' => $title,
                    // Send literal local time "2025-12-29T09:00:00"
                    // FullCalendar 'local' mode renders this exactly as is, ignoring offsets.
                    'start' => $booking->datetime->format('Y-m-d\TH:i:s'), 
                    'color' => $color,
                    'className' => $isArchived ? 'archived-event' : '',
                    'allDay' => false, // Ensure it appears in time grid
                    'extendedProps' => [
                         'fullname' => $booking->fullname,
                         'phone' => $booking->phone,
                         'telegram' => $booking->telegram,
                         'doctor' => is_numeric($rawDoctor) ? (int)$rawDoctor : $rawDoctor, 
                         'doctor_name' => $doctorName,
                         'source' => $booking->source,
                         'lang_pref' => $booking->lang_pref,
                         'datetime_raw' => $booking->datetime->format('Y-m-d\TH:i'),
                         'is_archived' => $isArchived
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
            'doctor' => 'required',
            'datetime' => 'required', // Remove 'date' to parse manually better
            'source' => 'required|string',
            'lang_pref' => 'required|string|max:10',
        ]);

        // Force parse in App Timezone (Tashkent)
        // Incoming format is likely "YYYY-MM-DDTHH:mm" (local)
        $date = \Carbon\Carbon::parse($validated['datetime'], config('app.timezone'));
        $validated['datetime'] = $date->format('Y-m-d H:i:s');

        // Check for overlaps if 'force' is not present or false
        if (!$request->boolean('force')) {
            $this->checkOverlap($validated['doctor'], $validated['datetime']);
        }

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
            'doctor' => 'required',
            'datetime' => 'required',
            'source' => 'required|string',
            'lang_pref' => 'required|string|max:10',
        ]);
        
        // Force parse in App Timezone
        $date = \Carbon\Carbon::parse($validated['datetime'], config('app.timezone'));
        $validated['datetime'] = $date->format('Y-m-d H:i:s');

        // Check for overlaps if 'force' is not present or false
        if (!$request->boolean('force')) {
            $this->checkOverlap($validated['doctor'], $validated['datetime'], $id);
        }

        $booking->update($validated);

        return response()->json($booking);
    }

    /**
     * Check for booking overlaps.
     * Throws 409 JSON response if overlap found.
     */
    protected function checkOverlap($doctor, $datetime, $ignoreId = null)
    {
        $newTime = \Carbon\Carbon::parse($datetime);
        
        // Interval: existing booking is within (NewTime - 60min) AND (NewTime + 60min)
        // Strictly less than 60 mins means:
        // ExistingTime > NewTime - 60min  AND  ExistingTime < NewTime + 60min
        
        $startRange = $newTime->copy()->subMinutes(60); // Exclusive bound logic handled by >
        $endRange = $newTime->copy()->addMinutes(60);   // Exclusive bound logic handled by <

        $query = Booking::where('doctor', $doctor)
            ->where('datetime', '>', $startRange)
            ->where('datetime', '<', $endRange);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $existing = $query->first();

        if ($existing) {
            // Fetch doctor name for friendly error
            $doctorName = $doctor;
            $doctorRecord = \App\Models\Doctor::find($doctor);
            if ($doctorRecord) {
                $doctorName = $doctorRecord->full_name;
            }

            // Return 409 Conflict with details
            response()->json([
                'message' => 'Double Booking detected',
                'conflict_data' => [
                    'doctor_name' => $doctorName,
                    'existing_time' => \Carbon\Carbon::parse($existing->datetime)->format('H:i'),
                    'existing_fullname' => $existing->fullname
                ]
            ], 409)->throwResponse();
        }
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

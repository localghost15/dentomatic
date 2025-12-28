<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{
    public function index()
    {
        return Doctor::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'calendar_color' => 'required|string|size:7', // #RRGGBB
            'photo' => 'nullable|image|max:2048', // 2MB
        ]);

        return DB::transaction(function () use ($request) {
            // Create User linked to Doctor
            $user = User::create([
                'name' => $request->full_name,
                'email' => 'doctor_' . uniqid() . '@dentomatic.com', // Safer email generation
                'password' => Hash::make('password'), // Default password
            ]);
            
            // Handle Photo Upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('doctors', 'public');
            }

            $doctor = Doctor::create([
                'user_id' => $user->id,
                'full_name' => $request->full_name,
                'specialization' => $request->specialization,
                'phone' => $request->phone,
                'calendar_color' => $request->calendar_color,
                'photo_path' => $photoPath,
                'status' => $request->status ?? 'active',
                'telegram_id' => $request->telegram_id,
            ]);

            return $doctor;
        });
    }

    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'calendar_color' => 'required|string|size:7',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($doctor->photo_path) {
                Storage::disk('public')->delete($doctor->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('doctors', 'public');
        }

        $doctor->update($data);
        
        // Update linked user name if needed
        if ($doctor->user) {
            $doctor->user->update(['name' => $request->full_name]);
        }

        return $doctor;
    }

    public function destroy(Doctor $doctor)
    {
        if ($doctor->photo_path) {
             Storage::disk('public')->delete($doctor->photo_path);
        }
        $doctor->delete();
        // Optionally delete user? Let's keep user for history or soft delete.
        return response()->noContent();
    }
}

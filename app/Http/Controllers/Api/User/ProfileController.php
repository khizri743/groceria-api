<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // Screen 1: Get Profile Data
    public function show(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            // Ensure settings have defaults if null
            'settings' => $request->user()->settings ?? [
                'notifications_orders' => true,
                'notifications_promos' => true,
                'dark_mode' => false,
                'language' => 'en'
            ]
        ]);
    }

    // Screen 2: Update Profile (Bio + Avatar)
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|string',
            'phone' => 'string|nullable',
            'date_of_birth' => 'date|nullable',
            'avatar' => 'nullable|image|max:2048', // Max 2MB
        ]);

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_url) {
                Storage::disk('public')->delete($user->avatar_url);
            }
            // Save new one
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_url = $path;
        }

        $user->update($request->only(['name', 'email', 'phone', 'date_of_birth']));

        return response()->json(['message' => 'Profile updated', 'user' => $user]);
    }

    // Screen: Settings & Notifications
    public function updateSettings(Request $request)
    {
        $request->validate([
            'notifications_orders' => 'boolean',
            'notifications_promos' => 'boolean',
            'dark_mode' => 'boolean',
            'language' => 'string'
        ]);

        $user = $request->user();
        
        // Merge new settings with old ones
        $currentSettings = $user->settings ?? [];
        $newSettings = array_merge($currentSettings, $request->all());
        
        $user->settings = $newSettings;
        $user->save();

        return response()->json(['message' => 'Settings saved', 'settings' => $user->settings]);
    }
}
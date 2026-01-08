<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Carbon\Carbon;

class MobilePasswordResetController extends Controller
{
    // Screen 10: User enters email, we send a code
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // If user not found, return 200 anyway (Security: don't reveal who is registered)
        if (!$user) {
            return response()->json(['message' => 'If this email exists, a code has been sent.']);
        }

        // Generate Code
        $code = rand(100000, 999999);
        
        // Save to DB
        OtpCode::create([
            'identifier' => $request->email,
            'code' => $code,
            'purpose' => 'password_reset',
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // TODO: Send Email here (Mail::to($user)...)
        // For local testing, we return the code in the response so you can see it.
        return response()->json([
            'message' => 'Code sent to email.',
            'dev_code' => $code // REMOVE THIS LINE IN PRODUCTION
        ]);
    }

    // Screen 5: Validate the code (Optional, but good for UI)
    public function validateCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric'
        ]);

        $exists = OtpCode::where('identifier', $request->email)
            ->where('code', $request->code)
            ->where('purpose', 'password_reset')
            ->where('expires_at', '>', Carbon::now())
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        return response()->json(['message' => 'Code is valid']);
    }

    // Screen 6: Set new password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Verify Code
        $otp = OtpCode::where('identifier', $request->email)
            ->where('code', $request->code)
            ->where('purpose', 'password_reset')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        // Find User and Reset Password
        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();
        }

        // Delete the used code
        $otp->delete();

        return response()->json(['message' => 'Password has been reset successfully.']);
    }
}
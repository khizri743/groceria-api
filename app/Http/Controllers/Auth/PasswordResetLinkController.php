<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
// use App\Mail\OtpCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Models\OtpCode; // Import your model
use Carbon\Carbon;
// use Illuminate\Support\Facades\Mail;

class PasswordResetLinkController extends Controller
{
    /**
     * Screen 10: Send the 6-digit code.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // 1. Check if user exists (fail silently for security, or return error)
        if (!$user) {
            return response()->json(['message' => 'If account exists, code sent.'], 200);
        }

        // 2. Generate 6-digit code
        $code = rand(100000, 999999);

        // 3. Save to database
        OtpCode::create([
            'identifier' => $request->email,
            'code' => $code,
            'purpose' => 'password_reset',
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);


        // try {
        //     Mail::to($request->email)->send(new OtpCodeMail($code));
        // } catch (\Exception $e) {
        //     return response()->json(['message' => 'Failed to send email. Check SMTP settings.'], 500);
        // }

        // 4. TODO: Send Email/SMS here
        
        return response()->json([
            'message' => 'Reset code sent to your email.',
            'dev_code' => $code // Remove this line in production!
        ]);
    }

    /**
     * Screen 5: Validate if the code is correct (before showing the password input).
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric'
        ]);

        // Check if code exists and is not expired
        $exists = OtpCode::where('identifier', $request->email)
            ->where('code', $request->code)
            ->where('purpose', 'password_reset')
            ->where('expires_at', '>', Carbon::now())
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'Invalid or expired code.'], 422);
        }

        return response()->json(['message' => 'Code is valid.']);
    }
}







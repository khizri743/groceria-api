<?php

// namespace App\Http\Controllers\Auth;

// use App\Http\Controllers\Controller;
// use Illuminate\Auth\Events\PasswordReset;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Password;
// use Illuminate\Support\Str;
// use Illuminate\Validation\Rules;
// use Illuminate\Validation\ValidationException;

// class NewPasswordController extends Controller
// {
//     /**
//      * Handle an incoming new password request.
//      *
//      * @throws \Illuminate\Validation\ValidationException
//      */
//     public function store(Request $request): JsonResponse
//     {
//         $request->validate([
//             'token' => ['required'],
//             'email' => ['required', 'email'],
//             'password' => ['required', 'confirmed', Rules\Password::defaults()],
//         ]);

//         // Here we will attempt to reset the user's password. If it is successful we
//         // will update the password on an actual user model and persist it to the
//         // database. Otherwise we will parse the error and return the response.
//         $status = Password::reset(
//             $request->only('email', 'password', 'password_confirmation', 'token'),
//             function ($user) use ($request) {
//                 $user->forceFill([
//                     'password' => Hash::make($request->string('password')),
//                     'remember_token' => Str::random(60),
//                 ])->save();

//                 event(new PasswordReset($user));
//             }
//         );

//         if ($status != Password::PASSWORD_RESET) {
//             throw ValidationException::withMessages([
//                 'email' => [__($status)],
//             ]);
//         }

//         return response()->json(['status' => __($status)]);
//     }
// }


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use App\Models\User;
use App\Models\OtpCode;
use Carbon\Carbon;

class NewPasswordController extends Controller
{
    /**
     * Screen 6: Reset the password using the code.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required'], // We use 'code' instead of 'token'
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 1. Verify the Code again
        $otp = OtpCode::where('identifier', $request->email)
            ->where('code', $request->code)
            ->where('purpose', 'password_reset')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otp) {
            return response()->json([
                'message' => 'Invalid or expired code.',
            ], 422);
        }

        // 2. Find User
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // 3. Update Password
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // 4. Delete the used OTP
        $otp->delete();

        event(new PasswordReset($user));

        return response()->json(['message' => 'Password reset successfully.']);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RecoverAccountRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\Api\UserResource;
use App\Mail\ForgotPasswordMail;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    /**
     * Registration
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
        ]);

        Group::create([
            'user_id' => $user->id,
            'title' => 'favourites',
        ]);

        Group::create([
            'user_id' => $user->id,
            'title' => 'scanned card',
        ]);

        $token = $user->createToken(getDeviceId()  ?: $user->email)->plainTextToken;
        return response()->json(
            [
                'message' => 'Account registered successfully',
                'user' => new UserResource($user),
                'token' => $token
            ]
        );
    }


    /**
     * Login
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email is not registered']);
        }

        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Password is incorrect']);
        }

        $token = $user->createToken(getDeviceId()  ?: $user->email)->plainTextToken;
        return response()->json(
            [
                'message' => 'Logged in successfully',
                'user' => new UserResource($user),
                'token' => $token
            ]
        );
    }

    /**
     * Forgot Password
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (!$user) {
            return response()->json(['message' => 'Email is not registered']);
        }

        $this->sendOtp($user->email);
        return response()->json(
            ['message' => 'Otp has been sent to email. Otp will expire after 5 minutes'],
        );
    }

    /**
     * Send Otp
     */
    private function sendOtp($email)
    {
        $email = trim($email);
        $otp = rand(1111, 9999);

        DB::table('password_resets')->where('email', $email)->delete();

        DB::table('password_resets')->insert([
            'email' =>  $email,
            'token' => $otp,
            'created_at' => now(),
        ]);

        Mail::to($email)->send(new ForgotPasswordMail($otp));
    }

    /**
     * Reset Password
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email is not registered']);
        }

        $verifyOtp = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->otp)
            ->first();

        if (!$verifyOtp) {
            return response()->json(['message' => 'Otp is not valid']);
        }

        if (now()->diffInMinutes($verifyOtp->created_at) > 5) {
            $verifyOtp = DB::table('password_resets')
                ->where('email', $email)
                ->where('token', $request->otp)
                ->delete();
            return response()->json(['message' => 'Otp expired']);
        }

        DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->otp)
            ->delete();

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        $user = User::where('email', $request->email)->first();

        $token = $user->createToken(getDeviceId()  ?: $user->email)->plainTextToken;

        return response()->json(['message' => 'Password set successfully', 'token' => $token]);
    }

    /**
     * Recove Account
     */
    public function recoverAccount(RecoverAccountRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email is not registered']);
        }

        if ($user->status == 0) {
            $updated = User::where('email', $request->email)->update(
                [
                    'status' => 1,
                    'deactivated_at' => null,
                ]
            );
            if ($updated) {
                return response()->json(['message' => 'Account recovered successfully']);
            }
        } else {
            return response()->json(['message' => 'Account is already activated']);
        }
        return response()->json(['message' => 'Something went wrong']);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged Out Successfully']);
    }
}

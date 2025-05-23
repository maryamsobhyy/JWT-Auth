<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function registerUser(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $otpCode = rand(100000, 999999);

        Otp::create([
            'email'      => $user->email,
            'otp_code'   => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        return [
            'user' => $user,
            'otp'  => $otpCode,
        ];
    }
    public function verifyOtp(string $email, string $otp): bool
    {
        $otpRecord = Otp::where('email', $email)
            ->where('otp_code', $otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return false;
        }

        $user = User::where('email', $email)->first();
        $user->email_verified_at = now();
        $user->save();

        $otpRecord->delete();

        return true;
    }
    public function login(array $data)
    {
        $credentials = [
            'email'    => $data['email'],
            'password' => $data['password'],
        ];
        if (!$token = auth('api')->attempt($credentials)) {
            return false;
        }
        $user = auth('api')->user();
        if (is_null($user->email_verified_at)) {
            auth('api')->logout();
            return false;
        }
        return $token;
    }
    public function changePassword(User $user, array $data): bool
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            return false;
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return true;
    }
    public function sendResetOtp(string $email): string
    {
        $user = User::where('email', $email)->first();
        Otp::where('email', $email)->delete();
        $otpCode = rand(100000, 999999);
        Otp::create([
            'email' => $email,
            'otp_code' => $otpCode,
            'expires_at' => now()->addMinutes(10),
        ]);
        //now i'm return this otp in code but the otp code send via email or any other way 
        return $otpCode;
    }
    public function resetPassword(string $email, string $otp, string $newPassword): bool
    {
        $otpRecord = Otp::where('email', $email)
            ->where('otp_code', $otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return false;
        }

        $user = User::where('email', $email)->first();
        $user->password = Hash::make($newPassword);
        $user->save();

        $otpRecord->delete();

        return true;
    }
}

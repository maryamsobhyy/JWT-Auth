<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignupRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\VerifyOtpRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;

class AuthController extends Controller
{
    public function signup(SignupRequest $request, AuthService $authService)
    {
        $result = $authService->registerUser($request->validated());

        return response()->json([
            'message' => 'User registered successfully. Please verify your email with the OTP sent.',
            'user'    => new UserResource($result['user']),
            'otp'     => $result['otp'], // for test only to make the code work without sending an email 
            //if we need to send an email we need to use 
            // Mail::to($user->email)->send(new SendOtpMail($otpCode));

        ], 201);
    }
    public function verifyOtp(VerifyOtpRequest $request, AuthService $authService)
    {
        $success = $authService->verifyOtp($request->email, $request->otp);

        if (!$success) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
    public function login(LoginRequest $request, AuthService $authService)
    {
        $token = $authService->login($request->validated());

        if (!$token) {
            return response()->json(['message' => 'Invalid credentials or email not verified'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,

        ]);
    }
    public function changePassword(ChangePasswordRequest $request, AuthService $authService)
    {
        $user = auth()->user();
        $success = $authService->changePassword($user, $request->validated());

        if (!$success) {
            return response()->json(['message' => 'Current password is incorrect.'], 400);
        }

        return response()->json(['message' => 'Password changed successfully.'], 200);
    }
    public function forgotPassword(ForgotPasswordRequest $request, AuthService $authService)
    {
        $otp = $authService->sendResetOtp($request->email);

        return response()->json([
            'message' => 'OTP sent successfully.',
            'otp' => $otp // this will not be there when we send an email
        ]);
    }
    public function resetPassword(ResetPasswordRequest $request, AuthService $authService)
    {
        $success = $authService->resetPassword(
            $request->email,
            $request->otp,
            $request->new_password
        );
        if (!$success) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }
        return response()->json(['message' => 'Password reset successfully']);
    }
}

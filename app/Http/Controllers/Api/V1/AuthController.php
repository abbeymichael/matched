<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\SendOtp;
use App\Actions\Auth\VerifyOtp;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function sendOtp(SendOtpRequest $request, SendOtp $action): JsonResponse
    {
        $phone = $action->handle($request->input('phone'));

        return response()->json(['phone' => $phone, 'message' => 'OTP sent.']);
    }

    public function verify(VerifyOtpRequest $request, VerifyOtp $action): JsonResponse
    {
        $user = $action->handle($request->input('phone'), $request->input('code'));

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'phone' => $user->phone,
                'profile_locked' => $user->profile_locked,
                'verification_status' => $user->verification_status,
            ],
        ]);
    }
}

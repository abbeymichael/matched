<?php

namespace App\Http\Controllers;

use App\Actions\Auth\SendOtp;
use App\Actions\Auth\VerifyOtp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function sendOtp(Request $request, SendOtp $action): RedirectResponse
    {
        $request->validate(['phone' => ['required', 'string', 'max:20']]);
        $request->session()->put('auth.phone', $action->handle($request->string('phone')->toString()));
        return to_route('otp.show');
    }

    public function verify(Request $request, VerifyOtp $action): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $action->handle((string) $request->session()->get('auth.phone'), $request->string('code')->toString());
        return to_route('dashboard');
    }
}

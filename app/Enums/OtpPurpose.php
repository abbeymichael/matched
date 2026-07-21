<?php

namespace App\Enums;

enum OtpPurpose: string
{
    case Login = 'login';
    case Signup = 'signup';
    case PasswordReset = 'password_reset';
}

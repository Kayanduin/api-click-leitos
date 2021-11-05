<?php

namespace App\Services;

use App\Mail\FirstPasswordMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function sendFirstPasswordMail(string $firstPassword, string $userEmail): void
    {
        $details = [
            'password' => $firstPassword
        ];

        Mail::to("clickleitos@gmail.com")->send(new FirstPasswordMail($details));
    }

    public function sendResetPasswordMail(string $userEmail): void
    {
        Mail::to("clickleitos@gmail.com")->send(new ResetPasswordMail([]));
    }
}
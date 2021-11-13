<?php

namespace App\Services;

use App\Mail\BedManagersNotificationMail;
use App\Mail\FirstPasswordMail;
use App\Mail\FreeBedNumberUpdateMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function sendFirstPasswordMail(string $firstPassword, string $userEmail, string $userName): void
    {
        $details = [
            'password' => $firstPassword,
            'user_name' => $userName
        ];

        Mail::to($userEmail)->send(new FirstPasswordMail($details));
    }

    public function sendResetPasswordMail(string $userEmail): void
    {
        Mail::to($userEmail)->send(new ResetPasswordMail());
    }

    public function sendBedManagersNotificationMail(string $managerEmail, string $bedType): void
    {
        $details = [
            'bed_type' => $bedType
        ];
        Mail::to($managerEmail)->send(new BedManagersNotificationMail($details));
    }

    public function sendFreeBedNumberUpdateMail(
        string $userEmail,
        string $actionType,
        string $healthUnitName,
        string $bedTypeName,
        int $totalBeds,
        int $freeBeds
    ) {
        $details = [
            'action_type' => $actionType,
            'health_unit_name' => $healthUnitName,
            'bed_type_name' => $bedTypeName,
            'total_beds' => $totalBeds,
            'free_beds' => $freeBeds,
        ];

        Mail::to($userEmail)->send(new FreeBedNumberUpdateMail($details));
    }
}

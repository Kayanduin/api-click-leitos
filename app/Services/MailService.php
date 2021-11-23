<?php

namespace App\Services;

use App\Mail\BedManagersNotificationMail;
use App\Mail\FirstPasswordMail;
use App\Mail\FreeBedNumberUpdateMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Sends an email to a specified email address with the first password of the user.
     * @param string $firstPassword
     * @param string $userEmail
     * @param string $userName
     */
    public function sendFirstPasswordMail(string $firstPassword, string $userEmail, string $userName): void
    {
        $details = [
            'password' => $firstPassword,
            'user_name' => $userName
        ];

        Mail::to($userEmail)->send(new FirstPasswordMail($details));
    }

    /**
     * Sends an email to the email address, informing that the password was reset successfully.
     * @param string $userEmail
     */
    public function sendResetPasswordMail(string $userEmail): void
    {
        Mail::to($userEmail)->send(new ResetPasswordMail());
    }

    /**
     * Sends an email to the email address, informing that an ambulance is on its way to the Health Unit and will need a
     * bed of the specified type.
     * @param string $managerEmail
     * @param string $bedType
     */
    public function sendBedManagersNotificationMail(string $managerEmail, string $bedType): void
    {
        $details = [
            'bed_type' => $bedType
        ];
        Mail::to($managerEmail)->send(new BedManagersNotificationMail($details));
    }

    /**
     * Sends an email to the email address, informing that a bed were updated in the specified Health Unit.
     * @param string $userEmail
     * @param string $actionType
     * @param string $healthUnitName
     * @param string $bedTypeName
     * @param int $totalBeds
     * @param int $freeBeds
     */
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

<?php

namespace App\Services\Staff;

use App\Models\UserEmergencyContact;

class UserEmergencyContactService
{

    public static function saveContacts(int $userId, array $contact): void
    {
        // Remove completely if all fields are empty
        if (empty(array_filter($contact))) {
            UserEmergencyContact::where('user_id', $userId)->delete();
            return;
        }

        // Save or update if at least one value exists
        UserEmergencyContact::updateOrCreate(
            ['user_id' => $userId],
            [
                'name'         => $contact['name'] ?? null,
                'relationship' => $contact['relationship'] ?? null,
                'phone_no'     => $contact['phone_no'] ?? null,
                'email'        => $contact['email'] ?? null,
            ]
        );
    }
}

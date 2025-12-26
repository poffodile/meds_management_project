<?php
namespace App\Services\Staff;

use App\Models\UserEmergencyContact;

class UserEmergencyContactService
{
    
    public static function saveContacts($userId, array $contact)
    {
        UserEmergencyContact::where('user_id', $userId)->delete();
      UserEmergencyContact::updateOrCreate(
            ['user_id' => $userId], // find existing by user_id
            [
                'name'         => $contact['name'],
                'relationship' => $contact['relationship'],
                'phone_no'     => $contact['phone_no'],
                'email'        => $contact['email'] ?? null,
            ]
        );
        
    }
}

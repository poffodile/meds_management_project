<?php

namespace App;

class SuperAdmin extends Admin
{
    protected $table = 'admin';

    public static function sendCredentials($super_admin_id = null, string $purpose = 'password_setup')
    {
        return Admin::sendCredentials($super_admin_id, $purpose);
    }
}

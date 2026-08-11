<?php

namespace App\Models;

/**
 * Frontend 4's authentication provider model.
 *
 * It reads the existing staff record without changing App\User, which remains
 * the model used by every legacy frontend and API.
 */
class Frontend4User extends \App\User
{
    protected $table = 'user';
}

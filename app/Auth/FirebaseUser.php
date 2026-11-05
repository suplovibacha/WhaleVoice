<?php

namespace App\Auth;

use Illuminate\Auth\GenericUser;

class FirebaseUser extends GenericUser
{
    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        // Firebase User không sử dụng Laravel remember token.
    }
}
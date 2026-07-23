<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class ActiveUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        $user = parent::retrieveByCredentials($credentials);

        if ($user && $user->Status !== 'ACTIVE') {
            return null;
        }

        return $user;
    }

    public function retrieveById($identifier)
    {
        $user = parent::retrieveById($identifier);

        if ($user && $user->Status !== 'ACTIVE') {
            return null;
        }

        return $user;
    }

    public function retrieveByToken($identifier, $token)
    {
        $user = parent::retrieveByToken($identifier, $token);

        if ($user && $user->Status !== 'ACTIVE') {
            return null;
        }

        return $user;
    }
}

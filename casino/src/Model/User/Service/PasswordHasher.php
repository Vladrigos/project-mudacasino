<?php

namespace App\Model\User\Service;

use RuntimeException;

class PasswordHasher
{
    public function hash(string $password): string
    {
        $hash = password_hash($password, PASSWORD_ARGON2I);
        if (false === $hash) {
            throw new RuntimeException('Unable to generate hash');
        }

        return $hash;
    }
}
<?php

namespace App\Model\User\UseCase\SignUp\Confirm;

class Command
{
    public $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }
}
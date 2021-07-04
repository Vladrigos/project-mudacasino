<?php

namespace App\Model\User\Entity\User;

use http\Exception\InvalidArgumentException;
use Webmozart\Assert\Assert;

class Email
{
    private $value;

    public function __construct($value)
    {
        Assert::notEmpty($value);

        if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Incorrect Email');
        }
        $this->value = mb_strtolower($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
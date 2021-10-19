<?php

namespace App\Tests\Unit\Model\User\Entity\User;

use App\Model\User\Entity\User\Role;
use App\Tests\Builder\User\UserBuilder;

class RoleTest extends \PHPUnit\Framework\TestCase
{
    public function testSuccess(): void
    {
        $user = (new UserBuilder())->viaEmail()->build();

        $user->changeRole(Role::admin());

        self::assertFalse($user->getRole()->isUser());
        self::assertTrue($user->getRole()->isAdmin());
    }

    public function testAlready(): void
    {
        $user = (new UserBuilder())->viaEmail()->build();

        $this->expectExceptionMessage('Role is already same.');

        $user->changeRole(Role::user());
    }
}
<?php

namespace App\Tests\Unit\Model\User\Entity\User\Network;

use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\Network;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = (new UserBuilder())
            ->viaNetwork($network = 'vk', $identity = '0000001')
            ->build();

        self::assertTrue($user->isActive());

        self::assertCount(1, $networks = $user->getNetworks());
        self::assertInstanceOf(Network::class, $first = reset($networks));
        self::assertEquals($network, $first->getNetwork());
        self::assertEquals($identity, $first->getIdentity());
    }

//    public function testAlready(): void
//    {
//        $user = (new UserBuilder())->viaNetwork($network = 'vk',$identity = '0000001')->build();
//
//        self::expectExceptionMessage('User is already signed up');
//
//        $user->signUpByNetwork(
//            $network,
//            $identity
//        );
//    }
}
<?php

namespace App\Model\User\UseCase\Network\Auth;

use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;
use App\Model\Flusher;

class Handler
{
    private $users;
    private $flusher;

    public function __construct(UserRepository $users, Flusher $flusher)
    {
        $this->users = $users;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        if ($this->users->hasByNetworkIdentity($command->network, $command->identity)) {
            throw new \DomainException('User already exists');
        }

        $user = User::signUpByNetwork(
            Id::next(),
            new \DateTimeImmutable(),
            $command->network,
            $command->identity,
        );

//        $user->signUpByNetwork($command->network, $command->identity);

        $this->users->add($user);

        $this->flusher->flush();
    }
}

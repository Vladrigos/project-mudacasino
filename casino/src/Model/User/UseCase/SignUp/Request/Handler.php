<?php

namespace App\Model\User\UseCase\SignUp\Request;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;
use App\Model\User\Flusher;
use App\Model\User\Service\ConfirmTokenizer;
use App\Model\User\Service\ConfirmTokenSender;
use App\Model\User\Service\PasswordHasher;

class Handler
{
    private UserRepository $users;
    private PasswordHasher $hasher;
    private Flusher $flusher;
    private ConfirmTokenizer $confirmTokenizer;
    private ConfirmTokenSender $confirmSender;

    public function __construct(UserRepository $users,
                                PasswordHasher $hasher,
                                ConfirmTokenizer $confirmTokenizer,
                                ConfirmTokenSender $sender,
                                Flusher $flusher)
    {
        $this->users = $users;
        $this->flusher = $flusher;
        $this->hasher = $hasher;
        $this->confirmTokenizer = $confirmTokenizer;
        $this->confirmSender = $sender;
    }

    public function handle(Command $command): void
    {
        $email = new Email($command->email);

        if($this->users->hasByEmail($email)){
            throw new \DomainException('User already exists!');
        }

        $user = User::signUpByEmail(
            Id::next(),
            new \DateTimeImmutable(),
            $email,
            $this->hasher->hash($command->password),
            $token = $this->confirmTokenizer->generate(),
        );

        $this->users->add($user);

        $this->confirmSender->send($email, $token);

        $this->flusher->flush();
    }
}
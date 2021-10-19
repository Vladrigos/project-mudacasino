<?php

namespace App\Model\User\Entity\User;

use App\Model\EntityNotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;

class UserRepository
{
    private EntityManager $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(User::class);
        $this->repository = $repository;
    }

    public function hasByEmail(Email $email): bool
    {
        return $this->repository->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.email = :email')
            ->setParameter(':email', $email->getValue())
            ->getQuery()->getSingleScalarResult() > 0;
    }

    public function add(User $user): void
    {
        $this->entityManager->persist($user);
    }

    public function findByConfirmToken(string $token): ?User
    {
        return $this->repository->findOneBy(['confirmToken' => $token]);
    }

    public function hasByNetworkIdentity(string $network, string $identity): bool
    {
        return $this->repository->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->innerJoin('t.networks', 'n')
            ->andWhere('n.networks = :network and n.identity = :identity')
            ->setParameter(':network', $network)
            ->setParameter(':identity', $identity)
            ->getQuery()->getSingleScalarResult() > 0;
    }

    public function getByEmail(Email $email): User
    {
        if (!$user = $this->repository->findOneBy(['email' => $email->getValue()])) {
            throw new EntityNotFoundException('User was not found');
        }

        return $this->repository->findBy(['email' => $email->getValue()]);
    }

    public function get(Id $id): User
    {
        if (!$user = $this->repository->find($id->getValue())) {
            throw new EntityNotFoundException('User was not found');
        }

        return $user;
    }

    public function findByResetToken(ResetToken $token): ?User
    {
        return $this->repository->findOneBy(['resetToken.token' => $token]);
    }
}
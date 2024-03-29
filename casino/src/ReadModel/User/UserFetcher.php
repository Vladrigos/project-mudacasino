<?php

namespace App\ReadModel\User;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class UserFetcher
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function existsByResetToken(string $token): bool
    {
        return $this->connection->createQueryBuilder()
            ->select('COUNT (*)')
            ->from('user_users')
            ->where('reset_token_token = :token')
            ->setParameter(':token', $token)
            ->execute()->fetchOne() > 0;
    }

    public function findForAuth(string $email): ?AuthView
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'email',
                'password_hash',
                'role',
                'status'
            )
            ->from('user_users')
            ->where('email = :email')
            ->setParameter(':email', $email)
            ->execute();

//      TODO
        $stmt->setFetchMode(FetchMode::CUSTOM_OBJECT, AuthView::class);
        $result = $stmt->fetch();

        return $result ?: null;
    }
}
<?php

namespace DMT\Test\AuthenticationService\Fixtures;

use DMT\AuthenticationService\Contracts\UserEntity;
use DMT\AuthenticationService\Contracts\UserTokenEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'User')]
#[ORM\UniqueConstraint(name: 'uniq_User_email', columns: ['email'])]
class User implements UserEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    #[ORM\Column(length: 180)]
    public string $email;

    #[ORM\Column(length: 40)]
    public string $username;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $password = null;

    public function isActive(): bool
    {
        return $this->id !== null;
    }

    public function addToken(UserTokenEntity $token): bool
    {
        return false;
    }
}

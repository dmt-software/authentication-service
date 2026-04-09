<?php

namespace DMT\Test\AuthenticationService\Fixtures;

use DMT\AuthenticationService\Contracts\UserTokenEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'UserToken')]
#[ORM\UniqueConstraint(name: 'uniq_user_token_token', columns: ['token'])]
class UserToken implements UserTokenEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    public string $token;

    #[ORM\Column(type: 'string', length: 50)]
    public string $reason;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(
        name: 'userId',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    public User $user;

    public function isValid(): bool
    {
        return $this->id !== null;
    }
}

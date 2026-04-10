<?php

declare(strict_types=1);

namespace DMT\Test\AuthenticationService\Fixtures;

use DateTimeImmutable;
use DMT\AuthenticationService\Contracts\TokenEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'UserToken')]
#[ORM\UniqueConstraint(name: 'uniq_user_token_token', columns: ['token'])]
class Token implements TokenEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    public string $token;

    #[ORM\Column(type: 'string', length: 50)]
    // phpcs:disable
    public TokenType $reason {
        set (TokenType|string $value) {
            $this->reason = $value instanceof TokenType ? $value : TokenType::tryFrom($value);
        }
    }
    // phpcs:enable

    public ?DateTimeImmutable $expiresAt;

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

    public function markUsed(): void
    {
    }
}

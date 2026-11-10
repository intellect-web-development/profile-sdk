<?php

declare(strict_types=1);

namespace IWD\ProfileSdk;

use DateTimeImmutable;

/** Профиль клиента своей системы */
final readonly class ProfileData
{
    public function __construct(
        public string $id,
        public int $userId,
        public ?string $name,
        public Gender $gender,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace IWD\ProfileSdk\Message\Event;

use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * Событие Profile: клиент создан. Адресовано одному Customer (routing key = его код).
 * Тонкое: только идентификатор. Данные клиента получатель берёт по ProfileClient.
 */
#[AsMessage(
    transport: 'profile-events-transport',
    serializedTypeName: 'profile.event.clientCreated',
)]
readonly class ClientCreatedMessage
{
    public function __construct(
        public int $userId,
        public string $integrationSystemCode,
    ) {
    }
}

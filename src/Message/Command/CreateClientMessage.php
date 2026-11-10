<?php

declare(strict_types=1);

namespace IWD\ProfileSdk\Message\Command;

use Symfony\Component\Messenger\Attribute\AsMessage;

/** Команда продукта сервису Profile: завести клиента для только что созданного пользователя. Идемпотентна */
#[AsMessage(
    transport: 'profile-commands-transport',
    serializedTypeName: 'profile.command.createClient',
)]
readonly class CreateClientMessage
{
    /**
     * @param int         $userId                Идентификатор пользователя в продукте
     * @param string|null $name                  Имя, если оно уже известно
     * @param string      $integrationSystemCode Код системы-отправителя (APP_SLUG), по нему Profile находит Customer
     */
    public function __construct(
        public int $userId,
        public ?string $name,
        public string $integrationSystemCode,
    ) {
    }
}

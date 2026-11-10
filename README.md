# profile-sdk

HTTP-клиент и контракты шины универсального сервиса [Profile](../profile). Пакет `intellect-web-development/profile-sdk`, namespace `IWD\ProfileSdk\`.

Чеклист подключения продукта: корневой [`../profile/INTEGRATION.md`](../profile/INTEGRATION.md).

```php
$client = new ProfileClient($httpClient, baseUrl: 'http://backend-profile.localhost', token: $personalJwt);

$client->read(userId: 42);                                  // ProfileData|null
$client->edit(userId: 42, name: 'Анна', gender: Gender::Female); // создаёт клиента, если его нет
```

Токен — персональный JWT системы (scope `profile:clients:read`, `profile:clients:write`, аудитория `profile`), его выдаёт integration-registry. Сервис отдаёт только клиентов системы из claim `sub`.

Сообщения Messenger (vhost `platform`):

| Класс | `serializedTypeName` | Exchange |
|-------|----------------------|----------|
| `Message\Command\CreateClientMessage` | `profile.command.createClient` | `profile.commands` |
| `Message\Event\ClientCreatedMessage` | `profile.event.clientCreated` | `profile.events` |
| `Message\Event\ClientUpdatedMessage` | `profile.event.clientUpdated` | `profile.events` |

Примеры wiring: [`examples/symfony/`](examples/symfony/).

Подключение: vcs-репозиторий в `composer.json` и зависимость `intellect-web-development/profile-sdk`. Path-репозиторий и копия в `packages/` не используются. В `symfony-skeleton` пакет по умолчанию не подключён.

# profile-sdk

HTTP-клиент универсального сервиса [Profile](../profile). Пакет `intellect-web-development/profile-sdk`, namespace `IWD\ProfileSdk\`.

```php
$client = new ProfileClient($httpClient, baseUrl: 'http://backend-profile.localhost', token: $personalJwt);

$client->read(userId: 42);                                  // ProfileData|null
$client->edit(userId: 42, name: 'Анна', gender: Gender::Female); // создаёт клиента, если его нет
```

Токен — персональный JWT системы (scope `profile:clients:read`, `profile:clients:write`, аудитория `profile`), его выдаёт integration-registry. Сервис отдаёт только клиентов системы из claim `sub`.

Подключение в продукте: vcs-репозиторий в `composer.json` и зависимость `intellect-web-development/profile-sdk` нужной версии. Path-репозиторий и копия в `packages/` не используются.

<?php

declare(strict_types=1);

namespace IWD\ProfileSdk;

use DateTimeImmutable;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Клиент универсального сервиса Profile.
 *
 * Токен — персональный JWT системы (выдаёт integration-registry): сервис берёт код системы из claim `sub`
 * и отдаёт только клиентов этой системы. Ошибки транспорта и ответы 401/403/5xx пробрасываются исключениями
 * Symfony HttpClient, обработка — на стороне вызывающего (адаптер продукта).
 */
final readonly class ProfileClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $token,
    ) {
    }

    /** Профиль пользователя продукта. null, если клиента ещё нет */
    public function read(int $userId): ?ProfileData
    {
        $response = $this->httpClient->request('GET', $this->url($userId), [
            'headers' => $this->headers(),
        ]);

        try {
            return $this->map($response->toArray());
        } catch (ClientExceptionInterface $exception) {
            if (404 === $exception->getResponse()->getStatusCode()) {
                return null;
            }

            throw $exception;
        }
    }

    /**
     * Меняет имя и пол. null оставляет поле как есть. Если клиента ещё нет, сервис создаёт его.
     */
    public function edit(int $userId, ?string $name = null, ?Gender $gender = null): ProfileData
    {
        $body = [];
        if (null !== $name) {
            $body['name'] = $name;
        }
        if (null !== $gender) {
            $body['gender'] = $gender->value;
        }

        $response = $this->httpClient->request('PUT', $this->url($userId), [
            'headers' => $this->headers(),
            'json' => (object) $body,
        ]);

        return $this->map($response->toArray());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function map(array $data): ProfileData
    {
        return new ProfileData(
            id: (string) $data['id'],
            userId: (int) $data['userId'],
            name: isset($data['name']) ? (string) $data['name'] : null,
            gender: Gender::from((string) $data['gender']),
            createdAt: new DateTimeImmutable((string) $data['createdAt']),
            updatedAt: new DateTimeImmutable((string) $data['updatedAt']),
        );
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    private function url(int $userId): string
    {
        return rtrim($this->baseUrl, '/') . '/api/sdk/clients/' . $userId;
    }
}

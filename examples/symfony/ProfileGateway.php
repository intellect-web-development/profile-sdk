<?php

declare(strict_types=1);

namespace IWD\ProfileSdk\Examples\Symfony;

use IWD\ProfileSdk\Gender;
use IWD\ProfileSdk\ProfileClient;
use IWD\ProfileSdk\ProfileData;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Пример адаптера продукта: единственная точка связи с Profile.
 * В продукте копируйте в App\Common\Profile (или свой BC) при opt-in; домен ProfileClient не импортирует.
 */
final readonly class ProfileGateway
{
    public function __construct(
        private ProfileClient $profileClient,
        private LoggerInterface $logger,
    ) {
    }

    public function find(int $userId): ?ProfileData
    {
        return $this->call(
            operation: 'read',
            userId: $userId,
            request: fn (): ?ProfileData => $this->profileClient->read($userId),
        );
    }

    public function edit(int $userId, ?string $name = null, ?Gender $gender = null): ProfileData
    {
        return $this->call(
            operation: 'edit',
            userId: $userId,
            request: fn (): ProfileData => $this->profileClient->edit(userId: $userId, name: $name, gender: $gender),
        );
    }

    /**
     * @template T
     *
     * @param callable(): T $request
     *
     * @return T
     */
    private function call(string $operation, int $userId, callable $request): mixed
    {
        try {
            return $request();
        } catch (HttpExceptionInterface $exception) {
            $this->logger->error('Profile request failed', [
                'operation' => $operation,
                'userId' => $userId,
                'status' => $exception->getResponse()->getStatusCode(),
            ]);
            throw $exception;
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Profile transport failed', [
                'operation' => $operation,
                'userId' => $userId,
                'error' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }
}

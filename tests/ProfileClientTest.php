<?php

declare(strict_types=1);

namespace IWD\ProfileSdk\Tests;

use IWD\ProfileSdk\Gender;
use IWD\ProfileSdk\ProfileClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

final class ProfileClientTest extends TestCase
{
    public function testReadSendsBearerAndMapsProfile(): void
    {
        $captured = [];
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'headers' => $options['headers']];

            return new MockResponse('{"id":"7","userId":42,"name":"Анна","gender":"female","createdAt":"2026-11-10T10:00:00+00:00","updatedAt":"2026-11-10T11:00:00+00:00"}', ['http_code' => 200]);
        });

        $profile = (new ProfileClient($http, 'http://profile.test/', 'jwt-token'))->read(42);

        self::assertSame('GET', $captured['method']);
        self::assertSame('http://profile.test/api/sdk/clients/42', $captured['url']);
        self::assertContains('Authorization: Bearer jwt-token', $captured['headers']);
        self::assertSame('7', $profile?->id);
        self::assertSame(42, $profile->userId);
        self::assertSame('2026-11-10T11:00:00+00:00', $profile->updatedAt->format(DATE_ATOM));
        self::assertSame('Анна', $profile->name);
        self::assertSame(Gender::Female, $profile->gender);
    }

    public function testGenderStatesListAllValues(): void
    {
        self::assertSame(['secret', 'male', 'female'], Gender::states());
    }

    public function testReadReturnsNullWhenClientMissing(): void
    {
        $http = new MockHttpClient(new MockResponse('{}', ['http_code' => 404]));

        self::assertNull((new ProfileClient($http, 'http://profile.test', 't'))->read(1));
    }

    public function testReadRethrowsForbidden(): void
    {
        $http = new MockHttpClient(new MockResponse('{}', ['http_code' => 403]));

        $this->expectException(ClientExceptionInterface::class);
        (new ProfileClient($http, 'http://profile.test', 't'))->read(1);
    }

    public function testEditSendsOnlyGivenFields(): void
    {
        $body = '';
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$body): MockResponse {
            $body = $options['body'];

            return new MockResponse('{"id":"7","userId":5,"name":"Мария","gender":"secret","createdAt":"2026-11-10T10:00:00+00:00","updatedAt":"2026-11-10T11:00:00+00:00"}', ['http_code' => 200]);
        });

        $profile = (new ProfileClient($http, 'http://profile.test', 't'))->edit(userId: 5, name: 'Мария');

        self::assertSame(['name' => 'Мария'], json_decode($body, true, 512, JSON_THROW_ON_ERROR));
        self::assertSame('Мария', $profile->name);
        self::assertSame(Gender::Secret, $profile->gender);
    }

    public function testEditSendsEmptyObjectWhenNothingGiven(): void
    {
        $body = '';
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$body): MockResponse {
            $body = $options['body'];

            return new MockResponse('{"id":"7","userId":5,"name":null,"gender":"secret","createdAt":"2026-11-10T10:00:00+00:00","updatedAt":"2026-11-10T11:00:00+00:00"}', ['http_code' => 200]);
        });

        (new ProfileClient($http, 'http://profile.test', 't'))->edit(userId: 5);

        self::assertSame('{}', $body);
    }

    public function testEditThrowsOnValidationError(): void
    {
        $http = new MockHttpClient(new MockResponse('{}', ['http_code' => 422]));

        $this->expectException(HttpExceptionInterface::class);
        (new ProfileClient($http, 'http://profile.test', 't'))->edit(userId: 5, gender: Gender::Male);
    }
}

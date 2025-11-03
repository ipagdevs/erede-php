<?php

namespace Rede;

use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    public function testGetService(): void
    {
        /** @var \Rede\AbstractAuthentication&\PHPUnit\Framework\MockObject\MockObject $authentication */
        $authentication = $this->createMock(BearerAuthentication::class);

        $credentials = new AuthenticationCredentials('client_id', 'client_secret');
        $service = new Service\AuthenticationService($authentication);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);
        $result = $method->invoke($service);
        $this->assertEquals('oauth2/token', $result);
    }

    public function testExecuteThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        // Test expects RuntimeException - either cURL error or authentication error

        /** @var \Rede\AbstractAuthentication&\PHPUnit\Framework\MockObject\MockObject $authentication */
        $authentication = $this->createMock(BearerAuthentication::class);

        $service = new Service\AuthenticationService($authentication);
        $service->execute();
    }

    public function testParseResponseThrowsException(): void
    {
        $this->expectException(\Exception::class);
        // Test expects exception - either RedeException or RuntimeException

        /** @var \Rede\AbstractAuthentication&\PHPUnit\Framework\MockObject\MockObject $authentication */
        $authentication = $this->createMock(BearerAuthentication::class);

        $service = new Service\AuthenticationService($authentication);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseResponse');
        $method->setAccessible(true);
        $method->invoke($service, '{}', 400);
    }

    public function testSendRequestThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        // Test expects RuntimeException - either cURL error or authentication error

        /** @var \Rede\AbstractAuthentication&\PHPUnit\Framework\MockObject\MockObject $authentication */
        $authentication = $this->createMock(BearerAuthentication::class);

        $service = new Service\AuthenticationService($authentication);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('sendRequest');
        $method->setAccessible(true);
        $method->invoke($service, 'POST', 'oauth2/token', []);
    }

    public function testGetServiceType(): void
    {
        /** @var \Rede\AbstractAuthentication&\PHPUnit\Framework\MockObject\MockObject $authentication */
        $authentication = $this->createMock(BearerAuthentication::class);

        $service = new Service\AuthenticationService($authentication);
        $this->assertInstanceOf(Service\AuthenticationService::class, $service);
    }

    public function testConstructorWithLogger(): void
    {
        /** @var \Rede\AbstractAuthentication&\PHPUnit\Framework\MockObject\MockObject $authentication */
        $authentication = $this->createMock(BearerAuthentication::class);

        /** @var \Psr\Log\LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $service = new Service\AuthenticationService($authentication, $logger);
        $this->assertInstanceOf(Service\AuthenticationService::class, $service);
    }
}

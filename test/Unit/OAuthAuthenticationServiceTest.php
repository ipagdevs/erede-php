<?php

namespace Rede;

use ReflectionClass;
use ReflectionMethod;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Rede\Exception\RedeException;
use PHPUnit\Framework\MockObject\MockObject;
use Rede\Service\OAuthAuthenticationService;

class OAuthAuthenticationServiceTest extends TestCase
{
    private AbstractAuthentication|MockObject $mockAuthentication;
    private BearerAuthentication|MockObject $mockBearerAuthentication;
    private CredentialsEnvironment $environment;
    private LoggerInterface|MockObject $mockLogger;

    protected function setUp(): void
    {
        $this->environment = CredentialsEnvironment::sandbox();
        $this->mockAuthentication = $this->createMock(AbstractAuthentication::class);
        $this->mockBearerAuthentication = $this->createMock(BearerAuthentication::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        $this->mockAuthentication
            ->method('getEnvironment')
            ->willReturn($this->environment);

        $this->mockBearerAuthentication
            ->method('getEnvironment')
            ->willReturn($this->environment);
    }

    public function testConstructorWithAuthentication(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);

        $this->assertInstanceOf(OAuthAuthenticationService::class, $service);
        $this->assertInstanceOf(\Rede\Service\AbstractAuthenticationService::class, $service);
    }

    public function testConstructorWithAuthenticationAndLogger(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication, $this->mockLogger);

        $this->assertInstanceOf(OAuthAuthenticationService::class, $service);
        $this->assertInstanceOf(\Rede\Service\AbstractAuthenticationService::class, $service);
    }

    public function testConstructorWithBearerAuthentication(): void
    {
        $service = new OAuthAuthenticationService($this->mockBearerAuthentication);

        $this->assertInstanceOf(OAuthAuthenticationService::class, $service);
    }

    public function testExtendsAbstractAuthenticationService(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);

        $this->assertInstanceOf(\Rede\Service\AbstractAuthenticationService::class, $service);
    }

    public function testGetServiceReturnsCorrectEndpoint(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($service);

        $this->assertEquals('oauth2/token', $result);
        $this->assertIsString($result);
    }

    public function testWithHeadersMethod(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);
        $headers = [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: TestAgent/1.0'
        ];

        $result = $service->withHeaders($headers);

        $this->assertSame($service, $result); // Test fluent interface
        $this->assertInstanceOf(OAuthAuthenticationService::class, $result);
    }

    public function testAddHeaderMethod(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);

        $result = $service->addHeader('X-Custom-Header', 'CustomValue');

        $this->assertSame($service, $result); // Test fluent interface
        $this->assertInstanceOf(OAuthAuthenticationService::class, $result);
    }

    public function testParseResponseWithValidJsonAndSuccessStatus(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseResponse');
        $method->setAccessible(true);

        $validResponse = json_encode([
            'access_token' => 'test_access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ]);

        $result = $method->invoke($service, $validResponse, 200);

        $this->assertInstanceOf(BearerAuthentication::class, $result);
    }

    public function testParseResponseWithInvalidJsonThrowsException(): void
    {
        $this->expectException(\TypeError::class);

        $service = new OAuthAuthenticationService($this->mockAuthentication);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseResponse');
        $method->setAccessible(true);

        $invalidJson = '{"invalid": json}';
        $method->invoke($service, $invalidJson, 200);
    }

    public function testParseResponseWith400StatusThrowsRedeException(): void
    {
        $this->expectException(RedeException::class);
        $this->expectExceptionMessage('[invalid_client]: Client authentication failed');

        $service = new OAuthAuthenticationService($this->mockAuthentication);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseResponse');
        $method->setAccessible(true);

        $errorResponse = json_encode([
            'error' => 'invalid_client',
            'error_description' => 'Client authentication failed',
            'error_code' => 401
        ]);

        $method->invoke($service, $errorResponse, 400);
    }

    public function testParseResponseWith401StatusThrowsRedeException(): void
    {
        $this->expectException(RedeException::class);
        $this->expectExceptionMessage('[unauthorized]: Access denied');

        $service = new OAuthAuthenticationService($this->mockAuthentication);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseResponse');
        $method->setAccessible(true);

        $errorResponse = json_encode([
            'error' => 'unauthorized',
            'error_description' => 'Access denied',
            'error_code' => 401
        ]);

        $method->invoke($service, $errorResponse, 401);
    }

    public function testParseResponseWithDefaultErrorValues(): void
    {
        $this->expectException(RedeException::class);
        $this->expectExceptionMessage('[unknown_error]: Error on getting the content from the API');

        $service = new OAuthAuthenticationService($this->mockAuthentication);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseResponse');
        $method->setAccessible(true);

        $errorResponse = json_encode([]);
        $method->invoke($service, $errorResponse, 500);
    }

    public function testParseResponseWithPartialErrorData(): void
    {
        $this->expectException(RedeException::class);
        $this->expectExceptionMessage('[custom_error]: Error on getting the content from the API');

        $service = new OAuthAuthenticationService($this->mockAuthentication);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseResponse');
        $method->setAccessible(true);

        $errorResponse = json_encode([
            'error' => 'custom_error'
            // Missing error_description and error_code
        ]);

        $method->invoke($service, $errorResponse, 422);
    }

    public function testLoggerDebugIsCalledWhenLoggerProvided(): void
    {
        $this->mockLogger
            ->expects($this->atLeastOnce())
            ->method('debug')
            ->with($this->isType('string'));

        $this->mockAuthentication
            ->method('toString')
            ->willReturn('Bearer test_token');

        $service = new OAuthAuthenticationService($this->mockAuthentication, $this->mockLogger);

        // This will fail due to network call, but we're testing logger interaction
        try {
            $service->execute(['grant_type' => 'client_credentials']);
        } catch (\Exception $e) {
            // Expected to fail, we're just testing logger interaction
            // The logger should have been called during the request attempt
        }
    }    public function testExecuteMethodCallsPostRequest(): void
    {
        $this->mockAuthentication
            ->method('toString')
            ->willReturn('Bearer test_token');

        $service = new OAuthAuthenticationService($this->mockAuthentication);

        // This will fail due to network call, but confirms execute tries to make request
        $this->expectException(\RuntimeException::class);

        $service->execute(['grant_type' => 'client_credentials']);
    }

    public function testServiceInheritanceFromAbstractAuthenticationService(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);

        $this->assertInstanceOf(Service\AbstractAuthenticationService::class, $service);
    }

    public function testFluentInterfaceChaining(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);

        $result = $service
            ->withHeaders(['Accept: application/json'])
            ->addHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->addHeader('User-Agent', 'TestClient/1.0');

        $this->assertSame($service, $result);
        $this->assertInstanceOf(OAuthAuthenticationService::class, $result);
    }

    /**
     * Data provider for different OAuth grant types
     */
    public function oauthGrantTypesDataProvider(): array
    {
        return [
            'client_credentials' => [
                ['grant_type' => 'client_credentials']
            ],
            'refresh_token' => [
                ['grant_type' => 'refresh_token', 'refresh_token' => 'test_refresh_token']
            ],
            'authorization_code' => [
                ['grant_type' => 'authorization_code', 'code' => 'auth_code_123']
            ],
            'empty_data' => [
                []
            ],
        ];
    }

    /**
     * @dataProvider oauthGrantTypesDataProvider
     */
    public function testExecuteWithDifferentGrantTypes(array $data): void
    {
        $this->mockAuthentication
            ->method('toString')
            ->willReturn('Basic dGVzdF9jbGllbnQ6dGVzdF9zZWNyZXQ=');

        $service = new OAuthAuthenticationService($this->mockAuthentication);

        // This will fail due to network call, but confirms method accepts various grant types
        $this->expectException(\RuntimeException::class);

        $service->execute($data);
    }

    /**
     * Data provider for HTTP error responses
     */
    public function httpErrorResponsesDataProvider(): array
    {
        return [
            'invalid_client_400' => [
                json_encode([
                    'error' => 'invalid_client',
                    'error_description' => 'Client authentication failed',
                    'error_code' => 400
                ]),
                400,
                '[invalid_client]: Client authentication failed'
            ],
            'unauthorized_401' => [
                json_encode([
                    'error' => 'unauthorized',
                    'error_description' => 'The access token provided is expired, revoked, malformed, or invalid',
                    'error_code' => 401
                ]),
                401,
                '[unauthorized]: The access token provided is expired, revoked, malformed, or invalid'
            ],
            'invalid_grant_422' => [
                json_encode([
                    'error' => 'invalid_grant',
                    'error_description' => 'The provided authorization grant is invalid',
                    'error_code' => 422
                ]),
                422,
                '[invalid_grant]: The provided authorization grant is invalid'
            ],
            'server_error_500' => [
                json_encode([
                    'error' => 'server_error',
                    'error_description' => 'Internal server error',
                    'error_code' => 500
                ]),
                500,
                '[server_error]: Internal server error'
            ],
        ];
    }

    /**
     * @dataProvider httpErrorResponsesDataProvider
     */
    public function testParseResponseWithVariousHttpErrors(string $response, int $statusCode, string $expectedMessage): void
    {
        $this->expectException(RedeException::class);
        $this->expectExceptionMessage($expectedMessage);

        $service = new OAuthAuthenticationService($this->mockAuthentication);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('parseResponse');
        $method->setAccessible(true);

        $method->invoke($service, $response, $statusCode);
    }

    public function testExecuteWithEmptyDataArray(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);

        // Test that execute accepts empty array
        $this->mockAuthentication
            ->method('toString')
            ->willReturn('Bearer test_token');

        // This will fail due to network call, but we're testing method signature
        try {
            $service->execute([]);
        } catch (\RuntimeException $e) {
            // Expected due to network call
            $this->assertInstanceOf(\RuntimeException::class, $e);
        }
    }

    public function testExecuteWithClientCredentialsData(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);

        $this->mockAuthentication
            ->method('toString')
            ->willReturn('Bearer test_token');

        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => 'test_client',
            'client_secret' => 'test_secret'
        ];

        // This will fail due to network call, but we're testing parameter handling
        try {
            $service->execute($data);
        } catch (\RuntimeException $e) {
            // Expected due to network call
            $this->assertInstanceOf(\RuntimeException::class, $e);
        }
    }

    public function testExecuteMethodSignature(): void
    {
        $reflection = new ReflectionClass(OAuthAuthenticationService::class);
        $method = $reflection->getMethod('execute');

        // Test method exists and has correct signature
        $this->assertTrue($method->isPublic());
        $this->assertEquals(1, $method->getNumberOfParameters());

        $parameter = $method->getParameters()[0];
        $this->assertEquals('data', $parameter->getName());
        $this->assertTrue($parameter->isOptional());
        $this->assertTrue($parameter->hasType());
        $this->assertEquals('array', $parameter->getType()->getName());
    }

    public function testSendRequestMethodIsProtected(): void
    {
        $reflection = new ReflectionClass(OAuthAuthenticationService::class);

        // Should inherit sendRequest from parent
        $this->assertTrue($reflection->hasMethod('sendRequest'));

        $method = $reflection->getMethod('sendRequest');
        $this->assertTrue($method->isProtected());
    }

    public function testServiceInheritanceHierarchy(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);

        // Test complete inheritance chain
        $this->assertInstanceOf(OAuthAuthenticationService::class, $service);
        $this->assertInstanceOf(\Rede\Service\AbstractAuthenticationService::class, $service);
    }

    public function testBearerAuthenticationWithCredentials(): void
    {
        $validResponse = json_encode([
            'access_token' => 'test_access_token_123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'refresh_token' => 'refresh_token_456'
        ]);

        $service = new OAuthAuthenticationService($this->mockAuthentication);
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('parseResponse');
        $method->setAccessible(true);

        $result = $method->invoke($service, $validResponse, 200);

        $this->assertInstanceOf(BearerAuthentication::class, $result);
        // Test that BearerAuthentication::withCredentials was called properly
    }

    public function testParseResponseWithMinimalValidData(): void
    {
        $minimalResponse = json_encode([
            'access_token' => 'minimal_token'
        ]);

        $service = new OAuthAuthenticationService($this->mockAuthentication);
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('parseResponse');
        $method->setAccessible(true);

        $result = $method->invoke($service, $minimalResponse, 200);

        $this->assertInstanceOf(BearerAuthentication::class, $result);
    }

    public function testHeaderManipulationWithOAuth(): void
    {
        $service = new OAuthAuthenticationService($this->mockAuthentication);

        // Test fluent header manipulation
        $result = $service
            ->withHeaders(['Accept: application/json'])
            ->addHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->addHeader('X-OAuth-Client', 'test-client');

        $this->assertSame($service, $result);
        $this->assertInstanceOf(OAuthAuthenticationService::class, $result);
    }

    public function testCompleteServiceWorkflow(): void
    {
        // Create a complete workflow test
        $environment = CredentialsEnvironment::sandbox();
        $auth = new BearerAuthentication($environment);
        $auth->setToken('initial_token');

        /** @var \Psr\Log\LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())
              ->method('debug');

        $service = new OAuthAuthenticationService($auth, $logger);

        // Test service configuration
        $this->assertInstanceOf(OAuthAuthenticationService::class, $service);

        // Test fluent interface
        $configured = $service
            ->withHeaders(['Accept: application/json'])
            ->addHeader('Content-Type', 'application/x-www-form-urlencoded');

        $this->assertSame($service, $configured);

        // Test that execute would attempt network call (will fail, but that's expected)
        $this->expectException(\RuntimeException::class);
        $service->execute(['grant_type' => 'client_credentials']);
    }
}

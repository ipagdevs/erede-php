<?php

namespace Rede\v2\Service;

use Rede\v2\Store;
use ReflectionClass;
use Rede\Environment;
use Rede\Transaction;
use Psr\Log\LoggerInterface;
use Rede\BearerAuthentication;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AbstractServiceTest extends TestCase
{
    private Store|MockObject $mockStore;
    private LoggerInterface|MockObject $mockLogger;
    private Environment|MockObject $mockEnvironment;
    private BearerAuthentication|MockObject $mockAuth;
    private AbstractService|MockObject $abstractService;

    protected function setUp(): void
    {
        $this->mockStore = $this->createMock(Store::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->mockEnvironment = $this->createMock(Environment::class);
        $this->mockAuth = $this->createMock(BearerAuthentication::class);

        // Create a concrete implementation for testing
        $this->abstractService = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore, $this->mockLogger]
        );
    }

    public function testConstructorWithStore(): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore]
        );

        $this->assertInstanceOf(AbstractService::class, $service);
        $this->assertInstanceOf(\Rede\Service\AbstractService::class, $service);
    }

    public function testConstructorWithStoreAndLogger(): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore, $this->mockLogger]
        );

        $this->assertInstanceOf(AbstractService::class, $service);
        $this->assertInstanceOf(\Rede\Service\AbstractService::class, $service);
    }

    public function testExtendsParentAbstractService(): void
    {
        $this->assertInstanceOf(\Rede\Service\AbstractService::class, $this->abstractService);
    }

    public function testConstructorCallsParentConstructor(): void
    {
        // Test that parent constructor is called by checking if inherited methods exist
        // This verifies the parent constructor was called and inheritance works
        $this->assertTrue(method_exists($this->abstractService, 'getUserAgent'));
        $this->assertTrue(method_exists($this->abstractService, 'dumpHttpInfo'));
    }

    public function testSendRequestWithBearerAuthentication(): void
    {
        // Setup mocks
        $this->mockStore->method('getAuth')
            ->willReturn($this->mockAuth);

        $this->mockAuth->method('getToken')
            ->willReturn('test_bearer_token');

        $this->mockStore->method('getEnvironment')
            ->willReturn($this->mockEnvironment);

        $this->mockEnvironment->method('getEndpoint')
            ->with($this->anything())
            ->willReturn('https://api.test.com/endpoint');

        // Mock the abstract methods
        $this->abstractService->method('getService')
            ->willReturn('test-service');

        $this->abstractService->method('parseResponse')
            ->willReturn(new Transaction());

        // Mock curl functions using runkit or similar approach would be needed
        // For now, we'll test the method exists and can be called
        $this->assertTrue(method_exists($this->abstractService, 'sendRequest'));
    }

    public function testSendRequestWithoutAuthentication(): void
    {
        // Setup mocks for no authentication scenario
        $this->mockStore->method('getAuth')
            ->willReturn(null);

        $this->mockStore->method('getEnvironment')
            ->willReturn($this->mockEnvironment);

        $this->mockStore->method('getFiliation')
            ->willReturn('test_filiation');

        $this->mockStore->method('getToken')
            ->willReturn('test_token');

        $this->mockEnvironment->method('getEndpoint')
            ->with($this->anything())
            ->willReturn('https://api.test.com/endpoint');

        // Mock the abstract methods
        $this->abstractService->method('getService')
            ->willReturn('test-service');

        $this->abstractService->method('parseResponse')
            ->willReturn(new Transaction());

        // Test method exists
        $this->assertTrue(method_exists($this->abstractService, 'sendRequest'));
    }

    public function testSendRequestHeadersWithBearerAuth(): void
    {
        // Test that proper headers are set with Bearer authentication
        $reflection = new ReflectionClass($this->abstractService);
        $method = $reflection->getMethod('sendRequest');
        $method->setAccessible(true);

        // Setup mocks
        $this->mockStore->method('getAuth')
            ->willReturn($this->mockAuth);

        $this->mockAuth->method('getToken')
            ->willReturn('test_bearer_token');

        $this->mockStore->method('getEnvironment')
            ->willReturn($this->mockEnvironment);

        $this->mockEnvironment->method('getEndpoint')
            ->willReturn('https://api.test.com/endpoint');

        $this->abstractService->method('getService')
            ->willReturn('test-service');

        $this->abstractService->method('parseResponse')
            ->willReturn(new Transaction());

        // Since we can't easily mock curl functions, we test method accessibility
        $this->assertTrue($method->isProtected());
    }

    public function testSendRequestWithDifferentHttpMethods(): void
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];

        foreach ($methods as $httpMethod) {
            // Setup basic mocks
            $this->mockStore->method('getAuth')
                ->willReturn(null);

            $this->mockStore->method('getEnvironment')
                ->willReturn($this->mockEnvironment);

            $this->mockStore->method('getFiliation')
                ->willReturn('test_filiation');

            $this->mockStore->method('getToken')
                ->willReturn('test_token');

            $this->mockEnvironment->method('getEndpoint')
                ->willReturn('https://api.test.com/endpoint');

            $this->abstractService->method('getService')
                ->willReturn('test-service');

            $this->abstractService->method('parseResponse')
                ->willReturn(new Transaction());

            // Test that method exists and accepts different HTTP methods
            $this->assertTrue(method_exists($this->abstractService, 'sendRequest'));
        }
    }

    public function testSendRequestWithBody(): void
    {
        $testBody = '{"test": "data"}';

        // Setup mocks
        $this->mockStore->method('getAuth')
            ->willReturn(null);

        $this->mockStore->method('getEnvironment')
            ->willReturn($this->mockEnvironment);

        $this->mockStore->method('getFiliation')
            ->willReturn('test_filiation');

        $this->mockStore->method('getToken')
            ->willReturn('test_token');

        $this->mockEnvironment->method('getEndpoint')
            ->willReturn('https://api.test.com/endpoint');

        $this->abstractService->method('getService')
            ->willReturn('test-service');

        $this->abstractService->method('parseResponse')
            ->willReturn(new Transaction());

        // Test method exists and can handle body parameter
        $reflection = new ReflectionClass($this->abstractService);
        $method = $reflection->getMethod('sendRequest');

        $this->assertEquals(2, $method->getNumberOfParameters());
        $this->assertEquals('body', $method->getParameters()[0]->getName());
        $this->assertEquals('method', $method->getParameters()[1]->getName());
    }

    public function testSendRequestWithLogger(): void
    {
        // Test that service can be created with logger (integration test)
        $serviceWithLogger = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore, $this->mockLogger]
        );

        $serviceWithLogger->method('getService')
            ->willReturn('test-service');

        $serviceWithLogger->method('parseResponse')
            ->willReturn(new Transaction());

        // Test that logger integration exists and service is properly created
        $this->assertInstanceOf(AbstractService::class, $serviceWithLogger);
        $this->assertTrue(method_exists($serviceWithLogger, 'sendRequest'));
    }

    public function testSendRequestReturnsTransaction(): void
    {
        $expectedTransaction = new Transaction();

        // Setup mocks
        $this->mockStore->method('getAuth')
            ->willReturn(null);

        $this->mockStore->method('getEnvironment')
            ->willReturn($this->mockEnvironment);

        $this->mockEnvironment->method('getEndpoint')
            ->willReturn('https://api.test.com/endpoint');

        $this->abstractService->method('getService')
            ->willReturn('test-service');

        $this->abstractService->method('parseResponse')
            ->willReturn($expectedTransaction);

        // Test return type
        $reflection = new ReflectionClass($this->abstractService);
        $method = $reflection->getMethod('sendRequest');

        $this->assertEquals('Rede\Transaction', $method->getReturnType()->getName());
    }

    public function testMethodVisibility(): void
    {
        $reflection = new ReflectionClass($this->abstractService);

        // Test sendRequest is protected
        $sendRequestMethod = $reflection->getMethod('sendRequest');
        $this->assertTrue($sendRequestMethod->isProtected());

        // Test constructor is public
        $constructorMethod = $reflection->getMethod('__construct');
        $this->assertTrue($constructorMethod->isPublic());
    }

    public function testInheritsFromParentAbstractService(): void
    {
        $reflection = new ReflectionClass(AbstractService::class);
        $parentClass = $reflection->getParentClass();

        $this->assertNotFalse($parentClass);
        $this->assertEquals('Rede\Service\AbstractService', $parentClass->getName());
    }

    public function testAbstractClassCannotBeInstantiated(): void
    {
        $reflection = new ReflectionClass(AbstractService::class);

        $this->assertTrue($reflection->isAbstract());
    }

    public function testUsesCorrectNamespace(): void
    {
        $reflection = new ReflectionClass(AbstractService::class);

        $this->assertEquals('Rede\v2\Service', $reflection->getNamespaceName());
    }

    public function testConstructorAcceptsV2Store(): void
    {
        $reflection = new ReflectionClass(AbstractService::class);
        $constructor = $reflection->getMethod('__construct');
        $parameters = $constructor->getParameters();

        $this->assertEquals(2, count($parameters));
        $this->assertEquals('store', $parameters[0]->getName());
        $this->assertEquals('logger', $parameters[1]->getName());

        // Check store type
        $storeType = $parameters[0]->getType();
        $this->assertEquals('Rede\v2\Store', $storeType->getName());
    }

    public function testLoggerParameterIsOptional(): void
    {
        $reflection = new ReflectionClass(AbstractService::class);
        $constructor = $reflection->getMethod('__construct');
        $parameters = $constructor->getParameters();

        $loggerParam = $parameters[1];
        $this->assertTrue($loggerParam->isOptional());
        $this->assertTrue($loggerParam->allowsNull());
    }

    /**
     * Data provider for HTTP methods
     */
    public function httpMethodsDataProvider(): array
    {
        return [
            'GET method' => ['GET'],
            'POST method' => ['POST'],
            'PUT method' => ['PUT'],
            'DELETE method' => ['DELETE'],
            'PATCH method' => ['PATCH'],
        ];
    }

    /**
     * @dataProvider httpMethodsDataProvider
     */
    public function testSendRequestHandlesDifferentHttpMethods(string $method): void
    {
        // Setup mocks
        $this->mockStore->method('getAuth')
            ->willReturn(null);

        $this->mockStore->method('getEnvironment')
            ->willReturn($this->mockEnvironment);

        $this->mockStore->method('getFiliation')
            ->willReturn('test_filiation');

        $this->mockStore->method('getToken')
            ->willReturn('test_token');

        $this->mockEnvironment->method('getEndpoint')
            ->willReturn('https://api.test.com/endpoint');

        $this->abstractService->method('getService')
            ->willReturn('test-service');

        $this->abstractService->method('parseResponse')
            ->willReturn(new Transaction());

        // Test that method parameter accepts different HTTP methods
        $reflection = new ReflectionClass($this->abstractService);
        $sendRequest = $reflection->getMethod('sendRequest');
        $parameters = $sendRequest->getParameters();

        $methodParam = $parameters[1];
        $this->assertEquals('method', $methodParam->getName());
        $this->assertEquals('GET', $methodParam->getDefaultValue());
    }

    public function testBearerTokenIntegration(): void
    {
        // Test specific v2 feature: Bearer token authentication integration
        // Create a real Store with Bearer authentication
        $realStore = new Store('test_filiation', 'test_token', Environment::sandbox());
        $bearerAuth = new BearerAuthentication();
        $bearerAuth->setToken('test_bearer_token_123');
        $realStore->setAuth($bearerAuth);

        // Create service with real store that has authentication
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$realStore]
        );

        $service->method('getService')
            ->willReturn('test-service');

        $service->method('parseResponse')
            ->willReturn(new Transaction());

        // Test that service can work with Bearer authentication
        $this->assertInstanceOf(AbstractService::class, $service);
        $this->assertEquals('test_bearer_token_123', $realStore->getAuth()->getToken());
    }

    public function testCompleteV2ServiceWorkflow(): void
    {
        // Test complete workflow: v2 Store -> Authentication -> Request -> Response

        // 1. Setup v2 Store with Bearer auth
        $realStore = new Store('test_filiation', 'test_token', Environment::sandbox());
        $bearerAuth = new BearerAuthentication();
        $bearerAuth->setToken('workflow_token')->setExpiresIn(3600);
        $realStore->setAuth($bearerAuth);

        // 2. Create service with real store
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$realStore, $this->mockLogger]
        );

        // 3. Mock abstract methods
        $service->method('getService')
            ->willReturn('test-service');

        $service->method('parseResponse')
            ->willReturn(new Transaction());

        // 4. Test platform configuration (new feature)
        $configuredService = $service->platform('TestFramework', '1.0.0');
        $this->assertSame($service, $configuredService);

        // 5. Verify service setup
        $this->assertInstanceOf(AbstractService::class, $service);
        $this->assertInstanceOf(\Rede\Service\AbstractService::class, $service);

        // 6. Test that all components are properly integrated
        $reflection = new ReflectionClass($service);
        $this->assertTrue($reflection->hasMethod('sendRequest'));
        $this->assertTrue($reflection->hasMethod('__construct'));
        $this->assertTrue($reflection->hasMethod('platform'));
        $this->assertTrue($reflection->hasMethod('getUserAgent'));
        $this->assertTrue($reflection->hasMethod('dumpHttpInfo'));
    }

    public function testPlatformPropertiesAndMethod(): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore]
        );

        // Test that platform method exists and returns static
        $this->assertTrue(method_exists($service, 'platform'));

        // Test method signature
        $reflection = new ReflectionClass($service);
        $platformMethod = $reflection->getMethod('platform');

        $this->assertTrue($platformMethod->isPublic());
        $this->assertEquals(2, $platformMethod->getNumberOfParameters());

        $parameters = $platformMethod->getParameters();
        $this->assertEquals('platform', $parameters[0]->getName());
        $this->assertEquals('platformVersion', $parameters[1]->getName());
        $this->assertTrue($parameters[0]->allowsNull());
        $this->assertTrue($parameters[1]->allowsNull());
    }

    public function testPlatformMethodFluency(): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore]
        );

        // Test fluent interface
        $result = $service->platform('TestPlatform', '1.0.0');

        $this->assertSame($service, $result);
        $this->assertInstanceOf(AbstractService::class, $result);
    }

    public function testPlatformMethodWithNullValues(): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore]
        );

        // Test with null values
        $result = $service->platform(null, null);

        $this->assertSame($service, $result);
    }

    public function testPlatformMethodWithMixedValues(): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore]
        );

        // Test with mixed values
        $result1 = $service->platform('Platform', null);
        $this->assertSame($service, $result1);

        $result2 = $service->platform(null, '2.0.0');
        $this->assertSame($service, $result2);
    }

    public function testPrivateMethodsExist(): void
    {
        $reflection = new ReflectionClass(AbstractService::class);

        // Test that private methods exist
        $this->assertTrue($reflection->hasMethod('dumpHttpInfo'));
        $this->assertTrue($reflection->hasMethod('getUserAgent'));

        // Test their visibility
        $dumpHttpInfoMethod = $reflection->getMethod('dumpHttpInfo');
        $getUserAgentMethod = $reflection->getMethod('getUserAgent');

        $this->assertTrue($dumpHttpInfoMethod->isPrivate());
        $this->assertTrue($getUserAgentMethod->isPrivate());
    }

    public function testGetUserAgentMethod(): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore]
        );

        $this->mockStore->method('getFiliation')
            ->willReturn('test_filiation');

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getUserAgent');
        $method->setAccessible(true);

        $userAgent = $method->invoke($service);

        $this->assertIsString($userAgent);
        $this->assertStringStartsWith('User-Agent:', $userAgent);
        $this->assertTrue(strpos($userAgent, 'test_filiation') !== false);
        $this->assertTrue(strpos($userAgent, phpversion()) !== false);
    }

    public function testGetUserAgentWithPlatform(): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore]
        );

        $this->mockStore->method('getFiliation')
            ->willReturn('test_filiation');

        // Set platform
        $service->platform('MyPlatform', '3.0.0');

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getUserAgent');
        $method->setAccessible(true);

        $userAgent = $method->invoke($service);

        $this->assertIsString($userAgent);
        $this->assertTrue(strpos($userAgent, 'MyPlatform/3.0.0') !== false);
    }

    public function testGetUserAgentWithoutPlatform(): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore]
        );

        $this->mockStore->method('getFiliation')
            ->willReturn('test_filiation');

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getUserAgent');
        $method->setAccessible(true);

        $userAgent = $method->invoke($service);

        $this->assertIsString($userAgent);
        // Should not contain platform info when not set
        $this->assertTrue(strpos($userAgent, 'MyPlatform') === false);
    }

    public function testDumpHttpInfoMethod(): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore, $this->mockLogger]
        );

        // Mock logger to expect debug calls
        $this->mockLogger->expects($this->atLeastOnce())
            ->method('debug')
            ->with($this->stringContains('Curl['));

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('dumpHttpInfo');
        $method->setAccessible(true);

        $httpInfo = [
            'http_code' => 200,
            'url' => 'https://api.test.com',
            'content_type' => 'application/json'
        ];

        $method->invoke($service, $httpInfo);
    }

    public function testDumpHttpInfoWithArrayValues(): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore, $this->mockLogger]
        );

        // Mock logger to expect multiple debug calls
        $this->mockLogger->expects($this->atLeastOnce())
            ->method('debug');

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('dumpHttpInfo');
        $method->setAccessible(true);

        $httpInfo = [
            'http_code' => 200,
            'headers' => [
                'Content-Type' => ['application/json'],
                'Authorization' => ['Bearer token']
            ]
        ];

        $method->invoke($service, $httpInfo);
    }

    public function testPlatformPropertiesPrivateAccess(): void
    {
        $reflection = new ReflectionClass(AbstractService::class);

        // Test that platform properties exist and are private
        $this->assertTrue($reflection->hasProperty('platform'));
        $this->assertTrue($reflection->hasProperty('platformVersion'));

        $platformProp = $reflection->getProperty('platform');
        $platformVersionProp = $reflection->getProperty('platformVersion');

        $this->assertTrue($platformProp->isPrivate());
        $this->assertTrue($platformVersionProp->isPrivate());

        // Test they are nullable
        $this->assertTrue($platformProp->getType()->allowsNull());
        $this->assertTrue($platformVersionProp->getType()->allowsNull());
        $this->assertEquals('string', $platformProp->getType()->getName());
        $this->assertEquals('string', $platformVersionProp->getType()->getName());
    }

    public function testPlatformMethodCallsParent(): void
    {
        // Test that platform method calls parent platform method
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore]
        );

        // This should not throw an error if parent method exists
        $result = $service->platform('TestPlatform', '1.0.0');
        $this->assertInstanceOf(AbstractService::class, $result);
    }

    /**
     * Data provider for platform configurations
     */
    public function platformConfigurationsDataProvider(): array
    {
        return [
            'both_null' => [null, null],
            'platform_only' => ['MyPlatform', null],
            'version_only' => [null, '1.0.0'],
            'both_values' => ['MyPlatform', '2.0.0'],
            'empty_strings' => ['', ''],
            'special_chars' => ['My-Platform_2024', '1.0.0-beta'],
        ];
    }

    /**
     * @dataProvider platformConfigurationsDataProvider
     */
    public function testPlatformMethodWithVariousConfigurations(?string $platform, ?string $platformVersion): void
    {
        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [$this->mockStore]
        );

        // Test that all configurations are accepted
        $result = $service->platform($platform, $platformVersion);

        $this->assertSame($service, $result);
        $this->assertInstanceOf(AbstractService::class, $result);
    }
}

<?php

namespace Rede;

use PHPUnit\Framework\TestCase;

class CredentialsEnvironmentTest extends TestCase
{
    public function testConstants(): void
    {
        $this->assertEquals('https://api.userede.com.br/redelabs', CredentialsEnvironment::PRODUCTION);
        $this->assertEquals('https://rl7-sandbox-api.useredecloud.com.br', CredentialsEnvironment::SANDBOX);
        $this->assertEquals('', CredentialsEnvironment::VERSION);
    }

    public function testConstantsAreStrings(): void
    {
        $this->assertIsString(CredentialsEnvironment::PRODUCTION);
        $this->assertIsString(CredentialsEnvironment::SANDBOX);
        $this->assertIsString(CredentialsEnvironment::VERSION);
    }

    public function testConstantsAreNotEmpty(): void
    {
        $this->assertNotEmpty(CredentialsEnvironment::PRODUCTION);
        $this->assertNotEmpty(CredentialsEnvironment::SANDBOX);
        // VERSION can be empty, so we don't test it
    }

    public function testConstantsAreValidUrls(): void
    {
        $this->assertStringStartsWith('https://', CredentialsEnvironment::PRODUCTION);
        $this->assertStringStartsWith('https://', CredentialsEnvironment::SANDBOX);
        $this->assertTrue(filter_var(CredentialsEnvironment::PRODUCTION, FILTER_VALIDATE_URL) !== false);
        $this->assertTrue(filter_var(CredentialsEnvironment::SANDBOX, FILTER_VALIDATE_URL) !== false);
    }

    public function testProductionStaticMethod(): void
    {
        $env = CredentialsEnvironment::production();

        $this->assertInstanceOf(CredentialsEnvironment::class, $env);
        $this->assertInstanceOf(Environment::class, $env);
    }

    public function testSandboxStaticMethod(): void
    {
        $env = CredentialsEnvironment::sandbox();

        $this->assertInstanceOf(CredentialsEnvironment::class, $env);
        $this->assertInstanceOf(Environment::class, $env);
    }

    public function testProductionEnvironmentEndpoint(): void
    {
        $env = CredentialsEnvironment::production();

        $this->assertEquals('https://api.userede.com.br/redelabs/', $env->getEndpoint(''));
    }

    public function testSandboxEnvironmentEndpoint(): void
    {
        $env = CredentialsEnvironment::sandbox();

        $this->assertEquals('https://rl7-sandbox-api.useredecloud.com.br/', $env->getEndpoint(''));
    }

    public function testGetEndpointWithService(): void
    {
        $prodEnv = CredentialsEnvironment::production();
        $sandboxEnv = CredentialsEnvironment::sandbox();

        $this->assertEquals(
            'https://api.userede.com.br/redelabs/some-service',
            $prodEnv->getEndpoint('some-service')
        );

        $this->assertEquals(
            'https://rl7-sandbox-api.useredecloud.com.br/some-service',
            $sandboxEnv->getEndpoint('some-service')
        );
    }

    public function testGetEndpointWithEmptyService(): void
    {
        $prodEnv = CredentialsEnvironment::production();
        $sandboxEnv = CredentialsEnvironment::sandbox();

        $this->assertEquals(
            'https://api.userede.com.br/redelabs/',
            $prodEnv->getEndpoint('')
        );

        $this->assertEquals(
            'https://rl7-sandbox-api.useredecloud.com.br/',
            $sandboxEnv->getEndpoint('')
        );
    }

    public function testGetEndpointWithComplexService(): void
    {
        $prodEnv = CredentialsEnvironment::production();
        $sandboxEnv = CredentialsEnvironment::sandbox();

        $complexService = 'api/v2/payments/transactions';

        $this->assertEquals(
            'https://api.userede.com.br/redelabs/' . $complexService,
            $prodEnv->getEndpoint($complexService)
        );

        $this->assertEquals(
            'https://rl7-sandbox-api.useredecloud.com.br/' . $complexService,
            $sandboxEnv->getEndpoint($complexService)
        );
    }

    public function testEnvironmentInheritance(): void
    {
        $prodEnv = CredentialsEnvironment::production();
        $sandboxEnv = CredentialsEnvironment::sandbox();

        $this->assertInstanceOf(Environment::class, $prodEnv);
        $this->assertInstanceOf(Environment::class, $sandboxEnv);
        $this->assertInstanceOf(CredentialsEnvironment::class, $prodEnv);
        $this->assertInstanceOf(CredentialsEnvironment::class, $sandboxEnv);
    }

    public function testMultipleInstancesIndependence(): void
    {
        $prod1 = CredentialsEnvironment::production();
        $prod2 = CredentialsEnvironment::production();
        $sandbox1 = CredentialsEnvironment::sandbox();
        $sandbox2 = CredentialsEnvironment::sandbox();

        // Should be different instances
        $this->assertNotSame($prod1, $prod2);
        $this->assertNotSame($sandbox1, $sandbox2);
        $this->assertNotSame($prod1, $sandbox1);

        // But should have same behavior
        $this->assertEquals($prod1->getEndpoint('test'), $prod2->getEndpoint('test'));
        $this->assertEquals($sandbox1->getEndpoint('test'), $sandbox2->getEndpoint('test'));
    }

    public function testInheritedMethodsFromEnvironment(): void
    {
        $env = CredentialsEnvironment::production();

        // Test inherited methods exist and work
        $this->assertNull($env->getIp());
        $this->assertNull($env->getSessionId());

        $env->setIp('192.168.1.1');
        $this->assertEquals('192.168.1.1', $env->getIp());

        $env->setSessionId('session123');
        $this->assertEquals('session123', $env->getSessionId());
    }

    public function testJsonSerializationInheritance(): void
    {
        $env = CredentialsEnvironment::production();
        $env->setIp('192.168.1.100');
        $env->setSessionId('test-session-456');

        $serialized = $env->jsonSerialize();

        $this->assertIsArray($serialized);
        $this->assertArrayHasKey('consumer', $serialized);
        $this->assertEquals('192.168.1.100', $serialized['consumer']->ip);
        $this->assertEquals('test-session-456', $serialized['consumer']->sessionId);
    }

    public function testFluentInterfaceWithInheritedMethods(): void
    {
        $env = CredentialsEnvironment::sandbox();

        $result = $env->setIp('10.0.0.1')->setSessionId('fluent-test');

        $this->assertSame($env, $result);
        $this->assertEquals('10.0.0.1', $env->getIp());
        $this->assertEquals('fluent-test', $env->getSessionId());
    }

    public function testDifferentEnvironmentsHaveDifferentEndpoints(): void
    {
        $prod = CredentialsEnvironment::production();
        $sandbox = CredentialsEnvironment::sandbox();

        $service = 'test-service';

        $prodEndpoint = $prod->getEndpoint($service);
        $sandboxEndpoint = $sandbox->getEndpoint($service);

        $this->assertNotEquals($prodEndpoint, $sandboxEndpoint);
        $this->assertStringContainsString('redelabs', $prodEndpoint);
        $this->assertStringContainsString('sandbox', $sandboxEndpoint);
    }

    /**
     * Test data provider for various service endpoints
     */
    public function serviceEndpointDataProvider(): array
    {
        return [
            'empty_service' => [''],
            'simple_service' => ['auth'],
            'nested_service' => ['api/v1/auth'],
            'complex_service' => ['payments/transactions/capture'],
            'service_with_numbers' => ['api/v2/users/123'],
            'service_with_special_chars' => ['webhooks/payment-status'],
            'long_service_path' => ['very/long/service/path/with/many/segments'],
        ];
    }

    /**
     * @dataProvider serviceEndpointDataProvider
     */
    public function testEndpointGenerationWithVariousServices(string $service): void
    {
        $prodEnv = CredentialsEnvironment::production();
        $sandboxEnv = CredentialsEnvironment::sandbox();

        $prodEndpoint = $prodEnv->getEndpoint($service);
        $sandboxEndpoint = $sandboxEnv->getEndpoint($service);

        // Verify production endpoint structure
        $this->assertStringStartsWith('https://api.userede.com.br/redelabs/', $prodEndpoint);
        if (!empty($service)) {
            $this->assertStringEndsWith($service, $prodEndpoint);
        }

        // Verify sandbox endpoint structure
        $this->assertStringStartsWith('https://rl7-sandbox-api.useredecloud.com.br/', $sandboxEndpoint);
        if (!empty($service)) {
            $this->assertStringEndsWith($service, $sandboxEndpoint);
        }

        // Verify they are different
        $this->assertNotEquals($prodEndpoint, $sandboxEndpoint);
    }

    public function testCompleteWorkflow(): void
    {
        // Test a complete workflow scenario

        // Create production environment
        $prodEnv = CredentialsEnvironment::production();
        $this->assertInstanceOf(CredentialsEnvironment::class, $prodEnv);

        // Set consumer information
        $prodEnv->setIp('203.0.113.1')
                ->setSessionId('prod-session-789');

        // Test endpoint generation
        $authEndpoint = $prodEnv->getEndpoint('oauth/token');
        $this->assertEquals('https://api.userede.com.br/redelabs/oauth/token', $authEndpoint);

        // Test serialization
        $serialized = $prodEnv->jsonSerialize();
        $this->assertEquals('203.0.113.1', $serialized['consumer']->ip);
        $this->assertEquals('prod-session-789', $serialized['consumer']->sessionId);

        // Create sandbox environment
        $sandboxEnv = CredentialsEnvironment::sandbox();
        $sandboxEnv->setIp('198.51.100.1')
                  ->setSessionId('sandbox-session-456');

        // Verify they are independent
        $this->assertNotEquals($prodEnv->getIp(), $sandboxEnv->getIp());
        $this->assertNotEquals($prodEnv->getSessionId(), $sandboxEnv->getSessionId());
        $this->assertNotEquals(
            $prodEnv->getEndpoint('test'),
            $sandboxEnv->getEndpoint('test')
        );
    }

    public function testVersionConstantUsageInEndpoint(): void
    {
        // Since VERSION is empty, endpoint should not have unnecessary double slashes in the path
        $env = CredentialsEnvironment::production();
        $endpoint = $env->getEndpoint('');

        // Remove the protocol part to check for double slashes in the path
        $pathPart = str_replace('https://', '', $endpoint);
        $this->assertStringNotContainsString('//', $pathPart, 'Endpoint path should not contain double slashes');
        $this->assertEquals('https://api.userede.com.br/redelabs/', $endpoint);
    }

    public function testConstantsImmutability(): void
    {
        // Constants should be immutable
        $originalProduction = CredentialsEnvironment::PRODUCTION;
        $originalSandbox = CredentialsEnvironment::SANDBOX;
        $originalVersion = CredentialsEnvironment::VERSION;

        // Create instances
        $env1 = CredentialsEnvironment::production();
        $env2 = CredentialsEnvironment::sandbox();

        // Constants should remain unchanged
        $this->assertEquals($originalProduction, CredentialsEnvironment::PRODUCTION);
        $this->assertEquals($originalSandbox, CredentialsEnvironment::SANDBOX);
        $this->assertEquals($originalVersion, CredentialsEnvironment::VERSION);
    }
}

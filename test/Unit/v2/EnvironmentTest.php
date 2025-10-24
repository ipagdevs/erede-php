<?php

namespace Rede\v2;

use ReflectionClass;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    public function testConstants(): void
    {
        $this->assertEquals('https://api.userede.com.br/erede', Environment::PRODUCTION);
        $this->assertEquals('https://sandbox-erede.useredecloud.com.br', Environment::SANDBOX);
        $this->assertEquals('v2', Environment::VERSION);
    }

    public function testConstantsAreStrings(): void
    {
        $this->assertIsString(Environment::PRODUCTION);
        $this->assertIsString(Environment::SANDBOX);
        $this->assertIsString(Environment::VERSION);
    }

    public function testConstantsAreNotEmpty(): void
    {
        $this->assertNotEmpty(Environment::PRODUCTION);
        $this->assertNotEmpty(Environment::SANDBOX);
        $this->assertNotEmpty(Environment::VERSION);
    }

    public function testConstantsAreValidUrls(): void
    {
        $this->assertStringStartsWith('https://', Environment::PRODUCTION);
        $this->assertStringStartsWith('https://', Environment::SANDBOX);
        $this->assertTrue(filter_var(Environment::PRODUCTION, FILTER_VALIDATE_URL) !== false);
        $this->assertTrue(filter_var(Environment::SANDBOX, FILTER_VALIDATE_URL) !== false);
    }

    public function testVersionConstantIsV2(): void
    {
        $this->assertEquals('v2', Environment::VERSION);
        $this->assertTrue(strpos(Environment::VERSION, 'v2') !== false);
    }

    public function testProductionStaticMethod(): void
    {
        $env = Environment::production();

        $this->assertInstanceOf(Environment::class, $env);
        $this->assertInstanceOf(\Rede\Environment::class, $env);
    }

    public function testSandboxStaticMethod(): void
    {
        $env = Environment::sandbox();

        $this->assertInstanceOf(Environment::class, $env);
        $this->assertInstanceOf(\Rede\Environment::class, $env);
    }

    public function testExtendsParentEnvironment(): void
    {
        $env = Environment::production();

        $this->assertInstanceOf(\Rede\Environment::class, $env);
    }

    public function testProductionEnvironmentEndpoint(): void
    {
        $env = Environment::production();

        $this->assertEquals('https://api.userede.com.br/erede/v2', $env->getEndpoint(''));
    }

    public function testSandboxEnvironmentEndpoint(): void
    {
        $env = Environment::sandbox();

        $this->assertEquals('https://sandbox-erede.useredecloud.com.br/v2', $env->getEndpoint(''));
    }

    public function testGetEndpointWithService(): void
    {
        $prodEnv = Environment::production();
        $sandboxEnv = Environment::sandbox();

        $this->assertEquals(
            'https://api.userede.com.br/erede/v2/transactions',
            $prodEnv->getEndpoint('/transactions')
        );

        $this->assertEquals(
            'https://sandbox-erede.useredecloud.com.br/v2/transactions',
            $sandboxEnv->getEndpoint('/transactions')
        );
    }

    public function testGetEndpointWithEmptyService(): void
    {
        $prodEnv = Environment::production();
        $sandboxEnv = Environment::sandbox();

        $this->assertEquals(
            'https://api.userede.com.br/erede/v2',
            $prodEnv->getEndpoint('')
        );

        $this->assertEquals(
            'https://sandbox-erede.useredecloud.com.br/v2',
            $sandboxEnv->getEndpoint('')
        );
    }

    public function testGetEndpointWithComplexService(): void
    {
        $prodEnv = Environment::production();
        $sandboxEnv = Environment::sandbox();

        $this->assertEquals(
            'https://api.userede.com.br/erede/v2/transactions/123/capture',
            $prodEnv->getEndpoint('/transactions/123/capture')
        );

        $this->assertEquals(
            'https://sandbox-erede.useredecloud.com.br/v2/transactions/123/capture',
            $sandboxEnv->getEndpoint('/transactions/123/capture')
        );
    }

    public function testGetEndpointWithServiceWithoutLeadingSlash(): void
    {
        $prodEnv = Environment::production();
        $sandboxEnv = Environment::sandbox();

        $this->assertEquals(
            'https://api.userede.com.br/erede/v2auth/token',
            $prodEnv->getEndpoint('auth/token')
        );

        $this->assertEquals(
            'https://sandbox-erede.useredecloud.com.br/v2auth/token',
            $sandboxEnv->getEndpoint('auth/token')
        );
    }

    public function testGetIpMethod(): void
    {
        $env = Environment::production();

        // Test that method exists and returns nullable string
        $this->assertTrue(method_exists($env, 'getIp'));
        $ip = $env->getIp();
        $this->assertTrue(is_null($ip) || is_string($ip));
    }

    public function testGetSessionIdMethod(): void
    {
        $env = Environment::sandbox();

        // Test that method exists and returns nullable string
        $this->assertTrue(method_exists($env, 'getSessionId'));
        $sessionId = $env->getSessionId();
        $this->assertTrue(is_null($sessionId) || is_string($sessionId));
    }

    public function testInheritedMethodsFromParent(): void
    {
        $env = Environment::production();

        // Test inherited methods exist
        $this->assertTrue(method_exists($env, 'getIp'));
        $this->assertTrue(method_exists($env, 'getSessionId'));

        // These should be inherited from parent and work
        $ip = $env->getIp();
        $sessionId = $env->getSessionId();

        $this->assertTrue(is_null($ip) || is_string($ip));
        $this->assertTrue(is_null($sessionId) || is_string($sessionId));
    }

    public function testConstructorIsPrivate(): void
    {
        $reflection = new ReflectionClass(Environment::class);
        $constructor = $reflection->getMethod('__construct');

        $this->assertTrue($constructor->isPrivate());
    }

    public function testStaticMethodsReturnSameClass(): void
    {
        $prodEnv = Environment::production();
        $sandboxEnv = Environment::sandbox();

        $this->assertInstanceOf(Environment::class, $prodEnv);
        $this->assertInstanceOf(Environment::class, $sandboxEnv);
    }

    public function testStaticMethodsReturnDifferentInstances(): void
    {
        $prodEnv1 = Environment::production();
        $prodEnv2 = Environment::production();

        // Should be different instances (not singleton)
        $this->assertNotSame($prodEnv1, $prodEnv2);

        $sandboxEnv1 = Environment::sandbox();
        $sandboxEnv2 = Environment::sandbox();

        $this->assertNotSame($sandboxEnv1, $sandboxEnv2);
    }

    public function testProductionAndSandboxAreDistinct(): void
    {
        $prodEnv = Environment::production();
        $sandboxEnv = Environment::sandbox();

        $this->assertNotEquals(
            $prodEnv->getEndpoint(''),
            $sandboxEnv->getEndpoint('')
        );

        $this->assertNotEquals(
            $prodEnv->getEndpoint('/test'),
            $sandboxEnv->getEndpoint('/test')
        );
    }

    public function testUsesCorrectNamespace(): void
    {
        $reflection = new ReflectionClass(Environment::class);

        $this->assertEquals('Rede\v2', $reflection->getNamespaceName());
        $this->assertEquals('Rede\v2\Environment', $reflection->getName());
    }

    public function testClassStructure(): void
    {
        $reflection = new ReflectionClass(Environment::class);

        // Test class is not abstract
        $this->assertFalse($reflection->isAbstract());

        // Test class is not final
        $this->assertFalse($reflection->isFinal());

        // Test extends parent
        $this->assertEquals('Rede\Environment', $reflection->getParentClass()->getName());
    }

    /**
     * Data provider for different service endpoints
     */
    public function serviceEndpointsDataProvider(): array
    {
        return [
            'empty_service' => ['', ''],
            'auth_service' => ['/auth/token', '/auth/token'],
            'transactions_service' => ['/transactions', '/transactions'],
            'capture_service' => ['/transactions/123/capture', '/transactions/123/capture'],
            'refund_service' => ['/transactions/456/refund', '/transactions/456/refund'],
            'no_leading_slash' => ['auth/token', 'auth/token'],
            'complex_path' => ['api/v1/merchants/123/stores', 'api/v1/merchants/123/stores'],
        ];
    }

    /**
     * @dataProvider serviceEndpointsDataProvider
     */
    public function testProductionEndpointWithVariousServices(string $service, string $expectedSuffix): void
    {
        $env = Environment::production();
        $expectedUrl = 'https://api.userede.com.br/erede/v2' . $expectedSuffix;

        $this->assertEquals($expectedUrl, $env->getEndpoint($service));
    }

    /**
     * @dataProvider serviceEndpointsDataProvider
     */
    public function testSandboxEndpointWithVariousServices(string $service, string $expectedSuffix): void
    {
        $env = Environment::sandbox();
        $expectedUrl = 'https://sandbox-erede.useredecloud.com.br/v2' . $expectedSuffix;

        $this->assertEquals($expectedUrl, $env->getEndpoint($service));
    }

    public function testEnvironmentComparison(): void
    {
        $prodEnv = Environment::production();
        $sandboxEnv = Environment::sandbox();

        // Different environments should produce different endpoints
        $this->assertNotEquals(
            $prodEnv->getEndpoint('/auth'),
            $sandboxEnv->getEndpoint('/auth')
        );
    }

    public function testEndpointUrlSecurity(): void
    {
        $prodEnv = Environment::production();
        $sandboxEnv = Environment::sandbox();

        // Both should use HTTPS
        $this->assertStringStartsWith('https://', $prodEnv->getEndpoint(''));
        $this->assertStringStartsWith('https://', $sandboxEnv->getEndpoint(''));
    }

    public function testV2SpecificFeatures(): void
    {
        // Test v2-specific constants and behavior
        $this->assertNotEquals('', Environment::VERSION, 'v2 should have a non-empty version');
        $this->assertEquals('v2', Environment::VERSION);

        // Test that URLs contain v2 path
        $prodEnv = Environment::production();
        $sandboxEnv = Environment::sandbox();

        $this->assertTrue(strpos($prodEnv->getEndpoint(''), '/v2') !== false);
        $this->assertTrue(strpos($sandboxEnv->getEndpoint(''), '/v2') !== false);
    }

    public function testDifferenceFromCredentialsEnvironment(): void
    {
        // Test that v2 Environment is different from CredentialsEnvironment
        $v2Prod = Environment::production();
        $credentialsProd = \Rede\CredentialsEnvironment::production();

        // Should be different URLs
        $this->assertNotEquals(
            $v2Prod->getEndpoint(''),
            $credentialsProd->getEndpoint('')
        );

        // v2 has version, CredentialsEnvironment has empty version
        $this->assertNotEquals(Environment::VERSION, \Rede\CredentialsEnvironment::VERSION);
    }

    public function testCompleteV2EnvironmentWorkflow(): void
    {
        // Test complete workflow: Create -> Configure -> Use

        // 1. Create environments
        $prodEnv = Environment::production();
        $sandboxEnv = Environment::sandbox();

        // 2. Verify they are correct instances
        $this->assertInstanceOf(Environment::class, $prodEnv);
        $this->assertInstanceOf(Environment::class, $sandboxEnv);
        $this->assertInstanceOf(\Rede\Environment::class, $prodEnv);
        $this->assertInstanceOf(\Rede\Environment::class, $sandboxEnv);

        // 3. Test endpoints for common v2 API calls
        $authEndpoint = $prodEnv->getEndpoint('/auth/token');
        $transactionEndpoint = $sandboxEnv->getEndpoint('/transactions');

        $this->assertTrue(strpos($authEndpoint, 'v2/auth/token') !== false);
        $this->assertTrue(strpos($transactionEndpoint, 'v2/transactions') !== false);

        // 4. Test inherited functionality
        $this->assertNull($prodEnv->getIp()); // Should be null by default
        $this->assertNull($sandboxEnv->getSessionId()); // Should be null by default

        // 5. Verify security (HTTPS)
        $this->assertStringStartsWith('https://', $authEndpoint);
        $this->assertStringStartsWith('https://', $transactionEndpoint);
    }

    public function testMethodReturnTypes(): void
    {
        $reflection = new ReflectionClass(Environment::class);

        // Test static method return types
        $productionMethod = $reflection->getMethod('production');
        $sandboxMethod = $reflection->getMethod('sandbox');

        $this->assertEquals('self', $productionMethod->getReturnType()->getName());
        $this->assertEquals('self', $sandboxMethod->getReturnType()->getName());

        // Test instance method return types
        $getEndpointMethod = $reflection->getMethod('getEndpoint');
        $getIpMethod = $reflection->getMethod('getIp');
        $getSessionIdMethod = $reflection->getMethod('getSessionId');

        $this->assertEquals('string', $getEndpointMethod->getReturnType()->getName());
        $this->assertTrue($getIpMethod->getReturnType()->allowsNull());
        $this->assertTrue($getSessionIdMethod->getReturnType()->allowsNull());
    }
}

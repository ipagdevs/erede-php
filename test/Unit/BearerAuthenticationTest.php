<?php

namespace Rede;

use PHPUnit\Framework\TestCase;

class BearerAuthenticationTest extends TestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $environment = CredentialsEnvironment::sandbox();
        $auth = new BearerAuthentication($environment);

        $this->assertEquals('Bearer', $auth->getType());
        $this->assertEquals('', $auth->getToken());
        $this->assertNull($auth->getExpiresIn());
        $this->assertEquals($environment, $auth->getEnvironment());
    }

    public function testConstructorWithDefaultEnvironment(): void
    {
        $auth = new BearerAuthentication();

        $this->assertEquals('Bearer', $auth->getType());
        $this->assertEquals('', $auth->getToken());
        $this->assertNull($auth->getExpiresIn());
        $this->assertInstanceOf(CredentialsEnvironment::class, $auth->getEnvironment());
    }

    public function testSettersAndGetters(): void
    {
        $auth = new BearerAuthentication();

        $token = 'abc123token';
        $expiresIn = 3600;
        $type = 'Bearer';

        $result = $auth->setToken($token);
        $this->assertSame($auth, $result); // Test fluent interface
        $this->assertEquals($token, $auth->getToken());

        $result = $auth->setExpiresIn($expiresIn);
        $this->assertSame($auth, $result); // Test fluent interface
        $this->assertEquals($expiresIn, $auth->getExpiresIn());

        $result = $auth->setType($type);
        $this->assertSame($auth, $result); // Test fluent interface
        $this->assertEquals($type, $auth->getType());
    }

    public function testSetTokenWithNull(): void
    {
        $auth = new BearerAuthentication();
        $auth->setToken('some-token');

        $auth->setToken(null);
        $this->assertEquals('', $auth->getToken());
    }

    public function testSetExpiresInWithNull(): void
    {
        $auth = new BearerAuthentication();
        $auth->setExpiresIn(3600);

        $auth->setExpiresIn(null);
        $this->assertNull($auth->getExpiresIn());
    }

    public function testGetCredentials(): void
    {
        $auth = new BearerAuthentication();
        $auth->setToken('test-token')
             ->setExpiresIn(7200)
             ->setType('Bearer');

        $credentials = $auth->getCredentials();

        $this->assertEquals([
            'type' => 'Bearer',
            'token' => 'test-token',
            'expires_in' => 7200,
        ], $credentials);
    }

    public function testGetCredentialsWithNullValues(): void
    {
        $auth = new BearerAuthentication();

        $credentials = $auth->getCredentials();

        $this->assertEquals([
            'type' => 'Bearer',
            'token' => '',
            'expires_in' => null,
        ], $credentials);
    }

    public function testWithCredentials(): void
    {
        $credentials = [
            'token_type' => 'Bearer',
            'access_token' => 'xyz789token',
            'expires_in' => 1800,
        ];

        $auth = BearerAuthentication::withCredentials($credentials);

        $this->assertEquals('Bearer', $auth->getType());
        $this->assertEquals('xyz789token', $auth->getToken());
        $this->assertEquals(1800, $auth->getExpiresIn());
    }

    public function testWithCredentialsPartialData(): void
    {
        $credentials = [
            'access_token' => 'partial-token',
        ];

        $auth = BearerAuthentication::withCredentials($credentials);

        $this->assertEquals('Bearer', $auth->getType()); // Default value
        $this->assertEquals('partial-token', $auth->getToken());
        $this->assertNull($auth->getExpiresIn());
    }

    public function testWithCredentialsEmptyArray(): void
    {
        $auth = BearerAuthentication::withCredentials([]);

        $this->assertEquals('Bearer', $auth->getType());
        $this->assertEquals('', $auth->getToken());
        $this->assertNull($auth->getExpiresIn());
    }

    public function testToString(): void
    {
        $auth = new BearerAuthentication();
        $auth->setToken('my-access-token');

        $result = $auth->toString();

        $this->assertEquals('Bearer my-access-token', $result);
    }

    public function testToStringWithEmptyToken(): void
    {
        $auth = new BearerAuthentication();

        $result = $auth->toString();

        $this->assertEquals('Bearer ', $result);
    }

    public function testToStringWithCustomType(): void
    {
        $auth = new BearerAuthentication();
        $auth->setToken('custom-token')
             ->setType('Custom');

        $result = $auth->toString();

        $this->assertEquals('Custom custom-token', $result);
    }

    public function testCompleteWorkflow(): void
    {
        // Test a complete workflow scenario
        $environment = CredentialsEnvironment::production();
        $auth = new BearerAuthentication($environment);

        // Set authentication data
        $auth->setToken('real-access-token')
             ->setExpiresIn(3600)
             ->setType('Bearer');

        // Verify all data is correct
        $this->assertEquals('Bearer', $auth->getType());
        $this->assertEquals('real-access-token', $auth->getToken());
        $this->assertEquals(3600, $auth->getExpiresIn());
        $this->assertEquals($environment, $auth->getEnvironment());

        // Test credentials array
        $credentials = $auth->getCredentials();
        $this->assertEquals([
            'type' => 'Bearer',
            'token' => 'real-access-token',
            'expires_in' => 3600,
        ], $credentials);

        // Test string representation
        $this->assertEquals('Bearer real-access-token', $auth->toString());
    }
}

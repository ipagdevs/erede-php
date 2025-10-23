<?php

namespace Rede;

use PHPUnit\Framework\TestCase;

class BasicAuthenticationTest extends TestCase
{
    private function createMockStore(string $filiation = '', string $token = ''): Store
    {
        /** @var \Rede\Store&\PHPUnit\Framework\MockObject\MockObject $store */
        $store = $this->createMock(Store::class);
        $store->method('getFiliation')->willReturn($filiation);
        $store->method('getToken')->willReturn($token);

        return $store;
    }

    public function testConstructorWithDefaultEnvironment(): void
    {
        $store = $this->createMockStore();
        $auth = new BasicAuthentication($store, null);

        $this->assertInstanceOf(CredentialsEnvironment::class, $auth->getEnvironment());
    }

    public function testConstructorWithSpecificEnvironment(): void
    {
        $store = $this->createMockStore();
        $environment = CredentialsEnvironment::sandbox();
        $auth = new BasicAuthentication($store, $environment);

        $this->assertEquals($environment, $auth->getEnvironment());
    }

    public function testGetUsernameAndPassword(): void
    {
        $store = $this->createMockStore('test_user', 'test_pass');
        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());

        $this->assertEquals('test_user', $auth->getUsername());
        $this->assertEquals('test_pass', $auth->getPassword());
    }

    public function testGetUsernameAndPasswordWithEmptyValues(): void
    {
        $store = $this->createMockStore('', '');
        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());

        $this->assertEquals('', $auth->getUsername());
        $this->assertEquals('', $auth->getPassword());
    }

    public function testSetUsername(): void
    {
        /** @var \Rede\Store&\PHPUnit\Framework\MockObject\MockObject $store */
        $store = $this->createMock(Store::class);
        $store->expects($this->once())
              ->method('setFiliation')
              ->with('new_user');

        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());
        $auth->setUsername('new_user');
    }

    public function testSetPassword(): void
    {
        /** @var \Rede\Store&\PHPUnit\Framework\MockObject\MockObject $store */
        $store = $this->createMock(Store::class);
        $store->expects($this->once())
              ->method('setToken')
              ->with('new_pass');

        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());
        $auth->setPassword('new_pass');
    }

    public function testSetUsernameAndPassword(): void
    {
        /** @var \Rede\Store&\PHPUnit\Framework\MockObject\MockObject $store */
        $store = $this->createMock(Store::class);
        $store->expects($this->once())
              ->method('setFiliation')
              ->with('new_user');
        $store->expects($this->once())
              ->method('setToken')
              ->with('new_pass');

        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());
        $auth->setUsername('new_user');
        $auth->setPassword('new_pass');
    }

    public function testGetCredentials(): void
    {
        $store = $this->createMockStore('cred_user', 'cred_pass');
        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());

        $credentials = $auth->getCredentials();

        $this->assertEquals([
            'username' => 'cred_user',
            'password' => 'cred_pass',
        ], $credentials);
    }

    public function testGetCredentialsWithEmptyValues(): void
    {
        $store = $this->createMockStore('', '');
        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());

        $credentials = $auth->getCredentials();

        $this->assertEquals([
            'username' => '',
            'password' => '',
        ], $credentials);
    }

    public function testToString(): void
    {
        $store = $this->createMockStore('string_user', 'string_pass');
        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());

        $result = $auth->toString();

        $this->assertStringStartsWith('Basic ', $result);

        // Verify the base64 encoded credentials
        $expectedCredentials = base64_encode('string_user:string_pass');
        $this->assertEquals('Basic ' . $expectedCredentials, $result);
    }

    public function testToStringWithEmptyCredentials(): void
    {
        $store = $this->createMockStore('', '');
        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());

        $result = $auth->toString();

        $this->assertStringStartsWith('Basic ', $result);

        // Verify the base64 encoded empty credentials
        $expectedCredentials = base64_encode(':');
        $this->assertEquals('Basic ' . $expectedCredentials, $result);
    }

    public function testToStringWithSpecialCharacters(): void
    {
        $store = $this->createMockStore('user@domain.com', 'p@ssw0rd!');
        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());

        $result = $auth->toString();

        $this->assertStringStartsWith('Basic ', $result);

        // Verify the base64 encoded credentials with special characters
        $expectedCredentials = base64_encode('user@domain.com:p@ssw0rd!');
        $this->assertEquals('Basic ' . $expectedCredentials, $result);
    }

    public function testToStringBase64Encoding(): void
    {
        $store = $this->createMockStore('testuser', 'testpass');
        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());

        $result = $auth->toString();

        // Remove "Basic " prefix to get just the encoded part
        $encodedPart = substr($result, 6);

        // Decode and verify it matches the original credentials
        $decodedCredentials = base64_decode($encodedPart);
        $this->assertEquals('testuser:testpass', $decodedCredentials);
    }

    public function testCompleteWorkflow(): void
    {
        // Test a complete workflow scenario
        $environment = CredentialsEnvironment::production();

        /** @var \Rede\Store&\PHPUnit\Framework\MockObject\MockObject $store */
        $store = $this->createMock(Store::class);

        // Set up initial mock behavior
        $store->method('getFiliation')->willReturn('initial_user');
        $store->method('getToken')->willReturn('initial_pass');

        $auth = new BasicAuthentication($store, $environment);

        // Verify initial state
        $this->assertEquals('initial_user', $auth->getUsername());
        $this->assertEquals('initial_pass', $auth->getPassword());
        $this->assertEquals($environment, $auth->getEnvironment());

        // Test credentials array
        $credentials = $auth->getCredentials();
        $this->assertEquals([
            'username' => 'initial_user',
            'password' => 'initial_pass',
        ], $credentials);

        // Test string representation
        $expectedEncoded = base64_encode('initial_user:initial_pass');
        $this->assertEquals('Basic ' . $expectedEncoded, $auth->toString());
    }

    public function testWithDifferentEnvironments(): void
    {
        $store = $this->createMockStore('env_user', 'env_pass');

        // Test with sandbox environment
        $sandboxAuth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());
        $this->assertEquals(CredentialsEnvironment::sandbox(), $sandboxAuth->getEnvironment());

        // Test with production environment
        $prodAuth = new BasicAuthentication($store, CredentialsEnvironment::production());
        $this->assertEquals(CredentialsEnvironment::production(), $prodAuth->getEnvironment());
    }

    public function testStoreInteraction(): void
    {
        /** @var \Rede\Store&\PHPUnit\Framework\MockObject\MockObject $store */
        $store = $this->createMock(Store::class);

        // Set up expectations for single calls
        $store->expects($this->once())
              ->method('getFiliation')
              ->willReturn('multi_user');

        $store->expects($this->once())
              ->method('getToken')
              ->willReturn('multi_pass');

        $auth = new BasicAuthentication($store, CredentialsEnvironment::sandbox());

        // Call methods that should trigger store methods
        $username = $auth->getUsername();
        $password = $auth->getPassword();

        $this->assertEquals('multi_user', $username);
        $this->assertEquals('multi_pass', $password);
    }
}

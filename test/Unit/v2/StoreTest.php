<?php

namespace Rede\v2;

use Rede\Environment;
use Rede\BasicAuthentication;
use Rede\BearerAuthentication;
use PHPUnit\Framework\TestCase;
use Rede\AbstractAuthentication;
use Rede\CredentialsEnvironment;
use PHPUnit\Framework\MockObject\MockObject;

class StoreTest extends TestCase
{
    private AbstractAuthentication|MockObject $mockAuth;
    private Environment $sandboxEnvironment;
    private Environment $productionEnvironment;

    protected function setUp(): void
    {
        $this->mockAuth = $this->createMock(AbstractAuthentication::class);
        $this->sandboxEnvironment = Environment::sandbox();
        $this->productionEnvironment = Environment::production();
    }

    public function testConstructorWithMinimalParameters(): void
    {
        $filiation = 'test_filiation';
        $token = 'test_token';

        $store = new Store($filiation, $token);

        $this->assertInstanceOf(Store::class, $store);
        $this->assertInstanceOf(\Rede\Store::class, $store);
        $this->assertEquals($filiation, $store->getFiliation());
        $this->assertEquals($token, $store->getToken());
        $this->assertNull($store->getAuth());
    }

    public function testConstructorWithEnvironment(): void
    {
        $filiation = 'test_filiation';
        $token = 'test_token';
        $environment = $this->sandboxEnvironment;

        $store = new Store($filiation, $token, $environment);

        $this->assertInstanceOf(Store::class, $store);
        $this->assertEquals($filiation, $store->getFiliation());
        $this->assertEquals($token, $store->getToken());
        $this->assertEquals($environment, $store->getEnvironment());
        $this->assertNull($store->getAuth());
    }

    public function testConstructorWithAllParameters(): void
    {
        $filiation = 'test_filiation';
        $token = 'test_token';
        $environment = $this->sandboxEnvironment;
        $auth = $this->mockAuth;

        $store = new Store($filiation, $token, $environment, $auth);

        $this->assertInstanceOf(Store::class, $store);
        $this->assertEquals($filiation, $store->getFiliation());
        $this->assertEquals($token, $store->getToken());
        $this->assertEquals($environment, $store->getEnvironment());
        $this->assertEquals($auth, $store->getAuth());
    }

    public function testExtendsOriginalStoreClass(): void
    {
        $store = new Store('test', 'test');

        $this->assertInstanceOf(\Rede\Store::class, $store);
    }

    public function testInheritedMethodsFromParent(): void
    {
        $store = new Store('test_filiation', 'test_token');

        // Test inherited methods exist
        $this->assertTrue(method_exists($store, 'getFiliation'));
        $this->assertTrue(method_exists($store, 'setFiliation'));
        $this->assertTrue(method_exists($store, 'getToken'));
        $this->assertTrue(method_exists($store, 'setToken'));
        $this->assertTrue(method_exists($store, 'getEnvironment'));
        $this->assertTrue(method_exists($store, 'setEnvironment'));
    }

    public function testGetAuthReturnsNull(): void
    {
        $store = new Store('test', 'test');

        $this->assertNull($store->getAuth());
    }

    public function testGetAuthReturnsSetAuthentication(): void
    {
        $store = new Store('test', 'test', null, $this->mockAuth);

        $this->assertEquals($this->mockAuth, $store->getAuth());
    }

    public function testSetAuthWithAuthentication(): void
    {
        $store = new Store('test', 'test');

        $result = $store->setAuth($this->mockAuth);

        $this->assertSame($store, $result); // Test fluent interface
        $this->assertEquals($this->mockAuth, $store->getAuth());
    }

    public function testSetAuthWithNull(): void
    {
        $store = new Store('test', 'test', null, $this->mockAuth);

        // Initially has auth
        $this->assertEquals($this->mockAuth, $store->getAuth());

        // Set to null
        $result = $store->setAuth(null);

        $this->assertSame($store, $result); // Test fluent interface
        $this->assertNull($store->getAuth());
    }

    public function testSetAuthWithDefaultParameter(): void
    {
        $store = new Store('test', 'test', null, $this->mockAuth);

        // Initially has auth
        $this->assertEquals($this->mockAuth, $store->getAuth());

        // Call without parameter (should default to null)
        $result = $store->setAuth();

        $this->assertSame($store, $result); // Test fluent interface
        $this->assertNull($store->getAuth());
    }

    public function testFluentInterface(): void
    {
        $store = new Store('test', 'test');

        $result = $store->setAuth($this->mockAuth);

        $this->assertSame($store, $result);
        $this->assertInstanceOf(Store::class, $result);
    }

    public function testWithBearerAuthentication(): void
    {
        $bearerAuth = new BearerAuthentication();
        $bearerAuth->setToken('test_bearer_token');

        $store = new Store('test_filiation', 'test_token');
        $store->setAuth($bearerAuth);

        $this->assertEquals($bearerAuth, $store->getAuth());
        $this->assertInstanceOf(BearerAuthentication::class, $store->getAuth());
    }

    public function testWithBasicAuthentication(): void
    {
        $mockStore = $this->createMock(\Rede\Store::class);
        $basicAuth = new BasicAuthentication($mockStore, CredentialsEnvironment::sandbox());

        $store = new Store('test_filiation', 'test_token');
        $store->setAuth($basicAuth);

        $this->assertEquals($basicAuth, $store->getAuth());
        $this->assertInstanceOf(BasicAuthentication::class, $store->getAuth());
    }

    public function testAuthenticationOverride(): void
    {
        $firstAuth = $this->createMock(AbstractAuthentication::class);
        $secondAuth = $this->createMock(AbstractAuthentication::class);

        $store = new Store('test', 'test', null, $firstAuth);

        // Initially has first auth
        $this->assertSame($firstAuth, $store->getAuth());

        // Override with second auth
        $store->setAuth($secondAuth);

        $this->assertSame($secondAuth, $store->getAuth());
        $this->assertNotSame($firstAuth, $store->getAuth());
    }

    public function testConstructorCallsParentConstructor(): void
    {
        $filiation = 'parent_test';
        $token = 'parent_token';
        $environment = $this->productionEnvironment;

        $store = new Store($filiation, $token, $environment);

        // Verify parent constructor was called by checking inherited properties
        $this->assertEquals($filiation, $store->getFiliation());
        $this->assertEquals($token, $store->getToken());
        $this->assertEquals($environment, $store->getEnvironment());
    }

    /**
     * Test data provider for different store configurations
     */
    public function storeConfigurationDataProvider(): array
    {
        return [
            'minimal_config' => [
                'filiation' => 'min_filiation',
                'token' => 'min_token',
                'environment' => null,
                'auth' => null,
            ],
            'with_sandbox_env' => [
                'filiation' => 'sandbox_filiation',
                'token' => 'sandbox_token',
                'environment' => Environment::sandbox(),
                'auth' => null,
            ],
            'with_production_env' => [
                'filiation' => 'prod_filiation',
                'token' => 'prod_token',
                'environment' => Environment::production(),
                'auth' => null,
            ],
            'with_auth_no_env' => [
                'filiation' => 'auth_filiation',
                'token' => 'auth_token',
                'environment' => null,
                'auth' => 'mock_auth',
            ],
            'full_config' => [
                'filiation' => 'full_filiation',
                'token' => 'full_token',
                'environment' => Environment::sandbox(),
                'auth' => 'mock_auth',
            ],
        ];
    }

    /**
     * @dataProvider storeConfigurationDataProvider
     */
    public function testVariousStoreConfigurations(string $filiation, string $token, ?Environment $environment, ?string $auth): void
    {
        $authInstance = $auth ? $this->mockAuth : null;

        $store = new Store($filiation, $token, $environment, $authInstance);

        $this->assertInstanceOf(Store::class, $store);
        $this->assertEquals($filiation, $store->getFiliation());
        $this->assertEquals($token, $store->getToken());

        if ($environment !== null) {
            $this->assertEquals($environment, $store->getEnvironment());
        } else {
            // Should have default environment from parent
            $this->assertInstanceOf(Environment::class, $store->getEnvironment());
        }

        if ($auth !== null) {
            $this->assertEquals($authInstance, $store->getAuth());
        } else {
            $this->assertNull($store->getAuth());
        }
    }

    public function testChainedMethodCalls(): void
    {
        $store = new Store('chain_test', 'chain_token');
        $newEnvironment = $this->sandboxEnvironment;
        $newFiliation = 'new_filiation';
        $newToken = 'new_token';

        // Test chained calls
        $result = $store
            ->setEnvironment($newEnvironment)
            ->setFiliation($newFiliation)
            ->setToken($newToken)
            ->setAuth($this->mockAuth);

        $this->assertSame($store, $result);
        $this->assertEquals($newEnvironment, $store->getEnvironment());
        $this->assertEquals($newFiliation, $store->getFiliation());
        $this->assertEquals($newToken, $store->getToken());
        $this->assertEquals($this->mockAuth, $store->getAuth());
    }

    public function testCompleteWorkflow(): void
    {
        // Test complete Store v2 workflow
        $filiation = 'workflow_test';
        $token = 'workflow_token';
        $environment = Environment::sandbox();

        // Create store
        $store = new Store($filiation, $token, $environment);

        // Verify initial state
        $this->assertInstanceOf(Store::class, $store);
        $this->assertInstanceOf(\Rede\Store::class, $store);
        $this->assertEquals($filiation, $store->getFiliation());
        $this->assertEquals($token, $store->getToken());
        $this->assertEquals($environment, $store->getEnvironment());
        $this->assertNull($store->getAuth());

        // Set authentication
        $auth = new BearerAuthentication();
        $auth->setToken('workflow_bearer_token')
             ->setExpiresIn(3600)
             ->setType('Bearer');

        $store->setAuth($auth);

        // Verify authentication is set
        $this->assertEquals($auth, $store->getAuth());
        $this->assertInstanceOf(BearerAuthentication::class, $store->getAuth());

        // Modify store properties
        $newEnvironment = Environment::production();
        $newFiliation = 'updated_filiation';
        $newToken = 'updated_token';

        $store->setEnvironment($newEnvironment)
              ->setFiliation($newFiliation)
              ->setToken($newToken);

        // Verify updates
        $this->assertEquals($newEnvironment, $store->getEnvironment());
        $this->assertEquals($newFiliation, $store->getFiliation());
        $this->assertEquals($newToken, $store->getToken());
        $this->assertEquals($auth, $store->getAuth()); // Auth should remain

        // Clear authentication
        $store->setAuth(null);
        $this->assertNull($store->getAuth());
    }

    public function testStoreWithDifferentAuthenticationTypes(): void
    {
        $store = new Store('auth_test', 'auth_token');

        // Test with BearerAuthentication
        $bearerAuth = new BearerAuthentication();
        $bearerAuth->setToken('bearer_token');

        $store->setAuth($bearerAuth);
        $this->assertInstanceOf(BearerAuthentication::class, $store->getAuth());

        // Test with BasicAuthentication
        $mockStore = $this->createMock(\Rede\Store::class);
        $basicAuth = new BasicAuthentication($mockStore, CredentialsEnvironment::sandbox());

        $store->setAuth($basicAuth);
        $this->assertInstanceOf(BasicAuthentication::class, $store->getAuth());
        $this->assertNotEquals($bearerAuth, $store->getAuth());

        // Test back to null
        $store->setAuth();
        $this->assertNull($store->getAuth());
    }

    public function testStoreIntegrationWithERedev2(): void
    {
        // Test integration between Store v2 and eRede v2
        $store = new Store('integration_test', 'integration_token', Environment::sandbox());

        // This would be used by eRede v2
        $this->assertInstanceOf(\Rede\Store::class, $store);
        $this->assertTrue(method_exists($store, 'getAuth'));
        $this->assertTrue(method_exists($store, 'setAuth'));

        // Test that it can store authentication for OAuth flows
        $auth = new BearerAuthentication();
        $auth->setToken('oauth_integration_token');

        $store->setAuth($auth);

        // Verify the store can provide auth to eRede v2
        $retrievedAuth = $store->getAuth();
        $this->assertInstanceOf(BearerAuthentication::class, $retrievedAuth);
        $this->assertEquals('oauth_integration_token', $retrievedAuth->getToken());
    }
}

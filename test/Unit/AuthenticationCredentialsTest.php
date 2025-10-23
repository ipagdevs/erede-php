<?php

namespace Rede;

use PHPUnit\Framework\TestCase;

class AuthenticationCredentialsTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $clientId = 'test_client_id';
        $clientSecret = 'test_client_secret';

        $credentials = new AuthenticationCredentials($clientId, $clientSecret);

        $this->assertEquals($clientId, $credentials->getClientId());
        $this->assertEquals($clientSecret, $credentials->getClientSecret());
    }

    public function testConstructorWithEmptyStrings(): void
    {
        $credentials = new AuthenticationCredentials('', '');

        $this->assertEquals('', $credentials->getClientId());
        $this->assertEquals('', $credentials->getClientSecret());
    }

    public function testConstructorWithSpecialCharacters(): void
    {
        $clientId = 'client@domain.com';
        $clientSecret = 'p@ssw0rd!#$%^&*()';

        $credentials = new AuthenticationCredentials($clientId, $clientSecret);

        $this->assertEquals($clientId, $credentials->getClientId());
        $this->assertEquals($clientSecret, $credentials->getClientSecret());
    }

    public function testConstructorWithLongStrings(): void
    {
        $clientId = str_repeat('a', 1000);
        $clientSecret = str_repeat('b', 1000);

        $credentials = new AuthenticationCredentials($clientId, $clientSecret);

        $this->assertEquals($clientId, $credentials->getClientId());
        $this->assertEquals($clientSecret, $credentials->getClientSecret());
        $this->assertEquals(1000, strlen($credentials->getClientId()));
        $this->assertEquals(1000, strlen($credentials->getClientSecret()));
    }

    public function testConstructorWithUnicodeCharacters(): void
    {
        $clientId = 'client_中文_עברית_العربية';
        $clientSecret = 'secret_🔐_💼_🚀';

        $credentials = new AuthenticationCredentials($clientId, $clientSecret);

        $this->assertEquals($clientId, $credentials->getClientId());
        $this->assertEquals($clientSecret, $credentials->getClientSecret());
    }

    public function testGettersReturnCorrectTypes(): void
    {
        $credentials = new AuthenticationCredentials('client_id', 'client_secret');

        $this->assertIsString($credentials->getClientId());
        $this->assertIsString($credentials->getClientSecret());
    }

    public function testImmutabilityOfStoredValues(): void
    {
        $clientId = 'original_client_id';
        $clientSecret = 'original_client_secret';

        $credentials = new AuthenticationCredentials($clientId, $clientSecret);

        // Modify the original variables
        $clientId = 'modified_client_id';
        $clientSecret = 'modified_client_secret';

        // Verify that the stored values remain unchanged
        $this->assertEquals('original_client_id', $credentials->getClientId());
        $this->assertEquals('original_client_secret', $credentials->getClientSecret());
    }

    public function testMultipleInstancesIndependence(): void
    {
        $credentials1 = new AuthenticationCredentials('client_1', 'secret_1');
        $credentials2 = new AuthenticationCredentials('client_2', 'secret_2');

        $this->assertEquals('client_1', $credentials1->getClientId());
        $this->assertEquals('secret_1', $credentials1->getClientSecret());
        $this->assertEquals('client_2', $credentials2->getClientId());
        $this->assertEquals('secret_2', $credentials2->getClientSecret());

        // Verify independence
        $this->assertNotEquals($credentials1->getClientId(), $credentials2->getClientId());
        $this->assertNotEquals($credentials1->getClientSecret(), $credentials2->getClientSecret());
    }

    public function testCredentialsWithWhitespace(): void
    {
        $clientId = '  client_id_with_spaces  ';
        $clientSecret = "\tclient_secret_with_tabs\n";

        $credentials = new AuthenticationCredentials($clientId, $clientSecret);

        // Should preserve whitespace as provided
        $this->assertEquals($clientId, $credentials->getClientId());
        $this->assertEquals($clientSecret, $credentials->getClientSecret());
    }

    public function testCredentialsWithNumericStrings(): void
    {
        $clientId = '123456789';
        $clientSecret = '987654321';

        $credentials = new AuthenticationCredentials($clientId, $clientSecret);

        $this->assertEquals($clientId, $credentials->getClientId());
        $this->assertEquals($clientSecret, $credentials->getClientSecret());
        $this->assertIsString($credentials->getClientId());
        $this->assertIsString($credentials->getClientSecret());
    }

    public function testCredentialsConsistencyAcrossMultipleCalls(): void
    {
        $credentials = new AuthenticationCredentials('consistent_client', 'consistent_secret');

        // Call getters multiple times to ensure consistency
        $clientId1 = $credentials->getClientId();
        $clientId2 = $credentials->getClientId();
        $clientId3 = $credentials->getClientId();

        $clientSecret1 = $credentials->getClientSecret();
        $clientSecret2 = $credentials->getClientSecret();
        $clientSecret3 = $credentials->getClientSecret();

        $this->assertEquals($clientId1, $clientId2);
        $this->assertEquals($clientId2, $clientId3);
        $this->assertEquals($clientSecret1, $clientSecret2);
        $this->assertEquals($clientSecret2, $clientSecret3);
    }

    public function testCompleteWorkflow(): void
    {
        // Simulate a real-world usage scenario
        $clientId = 'production_client_id_12345';
        $clientSecret = 'super_secure_secret_key_abcdef';

        // Create credentials
        $credentials = new AuthenticationCredentials($clientId, $clientSecret);

        // Verify all aspects
        $this->assertInstanceOf(AuthenticationCredentials::class, $credentials);
        $this->assertEquals($clientId, $credentials->getClientId());
        $this->assertEquals($clientSecret, $credentials->getClientSecret());
        $this->assertIsString($credentials->getClientId());
        $this->assertIsString($credentials->getClientSecret());
        $this->assertNotEmpty($credentials->getClientId());
        $this->assertNotEmpty($credentials->getClientSecret());
    }

    public function testEdgeCasesWithSingleCharacters(): void
    {
        $credentials = new AuthenticationCredentials('a', 'b');

        $this->assertEquals('a', $credentials->getClientId());
        $this->assertEquals('b', $credentials->getClientSecret());
        $this->assertEquals(1, strlen($credentials->getClientId()));
        $this->assertEquals(1, strlen($credentials->getClientSecret()));
    }

    public function testCredentialsWithSameValues(): void
    {
        $sameValue = 'identical_value';
        $credentials = new AuthenticationCredentials($sameValue, $sameValue);

        $this->assertEquals($sameValue, $credentials->getClientId());
        $this->assertEquals($sameValue, $credentials->getClientSecret());
        $this->assertEquals($credentials->getClientId(), $credentials->getClientSecret());
    }

    /**
     * Test data provider scenarios
     */
    public function credentialsDataProvider(): array
    {
        return [
            'standard_credentials' => ['client123', 'secret456'],
            'empty_credentials' => ['', ''],
            'special_chars' => ['client@test.com', 'secret#123!'],
            'long_strings' => [str_repeat('x', 255), str_repeat('y', 255)],
            'numeric_strings' => ['12345', '67890'],
            'mixed_case' => ['ClientID', 'SecretKEY'],
            'with_spaces' => ['client id', 'secret key'],
            'single_chars' => ['x', 'y'],
        ];
    }

    /**
     * @dataProvider credentialsDataProvider
     */
    public function testCredentialsWithVariousInputs(string $clientId, string $clientSecret): void
    {
        $credentials = new AuthenticationCredentials($clientId, $clientSecret);

        $this->assertEquals($clientId, $credentials->getClientId());
        $this->assertEquals($clientSecret, $credentials->getClientSecret());
        $this->assertIsString($credentials->getClientId());
        $this->assertIsString($credentials->getClientSecret());
    }
}

<?php

namespace Test\Unit\v2\Service;

use PHPUnit\Framework\TestCase;
use Rede\v2\Service\GetTransactionService;
use Rede\v2\Store;
use Rede\Transaction;
use Rede\Environment;
use Psr\Log\LoggerInterface;

class GetTransactionServiceTest extends TestCase
{
    private Store $store;
    private Transaction $transaction;
    private LoggerInterface $logger;
    private GetTransactionService $service;

    protected function setUp(): void
    {
        $this->store = new Store('filiation', 'password', Environment::sandbox());
        $this->transaction = new Transaction();
        $this->transaction->setTid('123456789');
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new GetTransactionService($this->store, $this->transaction, $this->logger);
    }

    public function testConstructor(): void
    {
        $service = new GetTransactionService($this->store, $this->transaction, $this->logger);

        $this->assertInstanceOf(GetTransactionService::class, $service);
    }

    public function testConstructorWithoutTransaction(): void
    {
        $service = new GetTransactionService($this->store);

        $this->assertInstanceOf(GetTransactionService::class, $service);
    }

    public function testConstructorWithoutLogger(): void
    {
        $service = new GetTransactionService($this->store, $this->transaction);

        $this->assertInstanceOf(GetTransactionService::class, $service);
    }

    public function testSetReference(): void
    {
        $reference = 'test-reference-123';
        
        $result = $this->service->setReference($reference);
        
        $this->assertSame($this->service, $result);
    }

    public function testSetRefund(): void
    {
        $result = $this->service->setRefund();
        
        $this->assertSame($this->service, $result);
    }

    public function testSetRefundWithFalse(): void
    {
        $result = $this->service->setRefund(false);
        
        $this->assertSame($this->service, $result);
    }

    public function testSetRefundWithTrue(): void
    {
        $result = $this->service->setRefund(true);
        
        $this->assertSame($this->service, $result);
    }

    public function testGetServiceWithReference(): void
    {
        $reference = 'test-ref-456';
        $this->service->setReference($reference);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($this->service);

        $this->assertEquals("transactions?reference={$reference}", $result);
    }

    public function testGetServiceWithRefund(): void
    {
        $this->service->setRefund(true);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($this->service);

        $this->assertEquals('transactions/123456789/refunds', $result);
    }

    public function testGetServiceWithTid(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($this->service);

        $this->assertEquals('transactions/123456789', $result);
    }

    public function testGetServicePriorityReferenceOverRefund(): void
    {
        $reference = 'priority-test';
        $this->service->setReference($reference);
        $this->service->setRefund(true);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($this->service);

        $this->assertEquals("transactions?reference={$reference}", $result);
    }

    public function testGetServicePriorityRefundOverTid(): void
    {
        $this->service->setRefund(true);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($this->service);

        $this->assertEquals('transactions/123456789/refunds', $result);
    }

    public function testFluentInterface(): void
    {
        $result = $this->service
            ->setReference('test-ref')
            ->setRefund(true)
            ->setTid('new-tid');

        $this->assertSame($this->service, $result);
    }

    public function testInheritsFromAbstractTransactionsService(): void
    {
        $this->assertInstanceOf(\Rede\v2\Service\AbstractTransactionsService::class, $this->service);
    }

    public function testExecuteMethodExists(): void
    {
        $this->assertTrue(method_exists($this->service, 'execute'));
    }

    public function testOverridesExecuteMethod(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('execute');
        
        $this->assertEquals(GetTransactionService::class, $method->getDeclaringClass()->getName());
    }

    public function testOverridesGetServiceMethod(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');
        
        $this->assertEquals(GetTransactionService::class, $method->getDeclaringClass()->getName());
    }

    /**
     * @dataProvider referenceProvider
     */
    public function testGetServiceWithDifferentReferences(string $reference, string $expected): void
    {
        $this->service->setReference($reference);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($this->service);

        $this->assertEquals($expected, $result);
    }

    public function referenceProvider(): array
    {
        return [
            'simple_reference' => ['ref123', 'transactions?reference=ref123'],
            'alphanumeric_reference' => ['abc123def', 'transactions?reference=abc123def'],
            'with_dashes' => ['ref-123-test', 'transactions?reference=ref-123-test'],
            'with_underscores' => ['ref_123_test', 'transactions?reference=ref_123_test'],
            'long_reference' => ['very-long-reference-12345', 'transactions?reference=very-long-reference-12345'],
        ];
    }

    /**
     * @dataProvider tidProvider
     */
    public function testGetServiceWithDifferentTids(string $tid, bool $refund, string $expected): void
    {
        // Criar uma nova transaction para cada teste
        $transaction = new Transaction();
        $transaction->setTid($tid);
        $service = new GetTransactionService($this->store, $transaction, $this->logger);
        $service->setRefund($refund);

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($service);

        $this->assertEquals($expected, $result);
    }

    public function tidProvider(): array
    {
        return [
            'simple_tid_no_refund' => ['123', false, 'transactions/123'],
            'simple_tid_with_refund' => ['123', true, 'transactions/123/refunds'],
            'long_tid_no_refund' => ['1234567890123456', false, 'transactions/1234567890123456'],
            'long_tid_with_refund' => ['1234567890123456', true, 'transactions/1234567890123456/refunds'],
            'alphanumeric_tid_no_refund' => ['abc123def456', false, 'transactions/abc123def456'],
            'alphanumeric_tid_with_refund' => ['abc123def456', true, 'transactions/abc123def456/refunds'],
        ];
    }

    public function testHasReferenceProperty(): void
    {
        $reflection = new \ReflectionClass($this->service);
        
        $this->assertTrue($reflection->hasProperty('reference'));
        $property = $reflection->getProperty('reference');
        $this->assertTrue($property->isPrivate());
    }

    public function testHasRefundProperty(): void
    {
        $reflection = new \ReflectionClass($this->service);
        
        $this->assertTrue($reflection->hasProperty('refund'));
        $property = $reflection->getProperty('refund');
        $this->assertTrue($property->isPrivate());
    }
}
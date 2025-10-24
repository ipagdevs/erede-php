<?php

namespace Test\Unit\v2\Service;

use PHPUnit\Framework\TestCase;
use Rede\v2\Service\CaptureTransactionService;
use Rede\v2\Store;
use Rede\Transaction;
use Rede\Environment;
use Rede\Exception\RedeException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use InvalidArgumentException;

class CaptureTransactionServiceTest extends TestCase
{
    private Store $store;
    private Transaction $transaction;
    private LoggerInterface $logger;
    private CaptureTransactionService $service;

    protected function setUp(): void
    {
        $this->store = new Store('filiation', 'password', Environment::sandbox());
        $this->transaction = new Transaction();
        $this->transaction->setTid('123456789');
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new CaptureTransactionService($this->store, $this->transaction, $this->logger);
    }

    public function testConstructor(): void
    {
        $service = new CaptureTransactionService($this->store, $this->transaction, $this->logger);

        $this->assertInstanceOf(CaptureTransactionService::class, $service);
    }

    public function testConstructorWithoutTransaction(): void
    {
        $service = new CaptureTransactionService($this->store);

        $this->assertInstanceOf(CaptureTransactionService::class, $service);
    }

    public function testConstructorWithoutLogger(): void
    {
        $service = new CaptureTransactionService($this->store, $this->transaction);

        $this->assertInstanceOf(CaptureTransactionService::class, $service);
    }

    public function testExecuteThrowsRuntimeExceptionOnInvalidJson(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Problem converting the Transaction object to json');

        // Create a transaction with circular reference to force json_encode to fail
        $transaction = new class extends Transaction {
            public function jsonSerialize(): mixed
            {
                return NAN; // This will cause json_encode to return false
            }
        };

        $service = new CaptureTransactionService($this->store, $transaction, $this->logger);
        
        // Use reflection to call the execute method since it's protected
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);
        $method->invoke($service);
    }

    public function testGetServiceWithTransaction(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($this->service);

        $this->assertEquals('transactions/123456789', $result);
    }

    public function testGetServiceWithoutTransaction(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transaction was not defined yet');

        $service = new CaptureTransactionService($this->store);
        
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);
        $method->invoke($service);
    }

    public function testGetServiceWithTransactionWithoutTid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transaction was not defined yet');

        $transactionWithoutTid = new Transaction();
        $service = new CaptureTransactionService($this->store, $transactionWithoutTid);
        
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);
        $method->invoke($service);
    }

    public function testGetServiceUsesParentGetService(): void
    {
        $tid = 'custom-tid-123';
        $this->transaction->setTid($tid);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($this->service);

        $this->assertEquals("transactions/{$tid}", $result);
    }

    public function testInheritsFromAbstractTransactionsService(): void
    {
        $this->assertInstanceOf(\Rede\v2\Service\AbstractTransactionsService::class, $this->service);
    }

    public function testCanSetAndGetTid(): void
    {
        $tid = 'new-tid-789';
        
        $result = $this->service->setTid($tid);
        
        $this->assertSame($this->service, $result);
        $this->assertEquals($tid, $this->service->getTid());
    }

    public function testExecuteMethodExists(): void
    {
        $this->assertTrue(method_exists($this->service, 'execute'));
    }

    public function testGetServiceMethodIsProtected(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');

        $this->assertTrue($method->isProtected());
    }

    /**
     * @dataProvider tidProvider
     */
    public function testGetServiceWithDifferentTids(string $tid, string $expected): void
    {
        $this->transaction->setTid($tid);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($this->service);

        $this->assertEquals($expected, $result);
    }

    public function tidProvider(): array
    {
        return [
            'simple_tid' => ['123', 'transactions/123'],
            'long_tid' => ['1234567890123456', 'transactions/1234567890123456'],
            'alphanumeric_tid' => ['abc123def456', 'transactions/abc123def456'],
            'with_dashes' => ['123-456-789', 'transactions/123-456-789'],
            'with_underscores' => ['test_tid_123', 'transactions/test_tid_123'],
        ];
    }
}
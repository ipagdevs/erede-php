<?php

namespace Test\Unit\v2\Service;

use PHPUnit\Framework\TestCase;
use Rede\v2\Service\CreateTransactionService;
use Rede\v2\Store;
use Rede\Transaction;
use Rede\Environment;
use Psr\Log\LoggerInterface;

class CreateTransactionServiceTest extends TestCase
{
    private Store $store;
    private Transaction $transaction;
    private LoggerInterface $logger;
    private CreateTransactionService $service;

    protected function setUp(): void
    {
        $this->store = new Store('filiation', 'password', Environment::sandbox());
        $this->transaction = new Transaction();
        $this->transaction->setTid('123456789');
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new CreateTransactionService($this->store, $this->transaction, $this->logger);
    }

    public function testConstructor(): void
    {
        $service = new CreateTransactionService($this->store, $this->transaction, $this->logger);

        $this->assertInstanceOf(CreateTransactionService::class, $service);
    }

    public function testConstructorWithoutTransaction(): void
    {
        $service = new CreateTransactionService($this->store);

        $this->assertInstanceOf(CreateTransactionService::class, $service);
    }

    public function testConstructorWithoutLogger(): void
    {
        $service = new CreateTransactionService($this->store, $this->transaction);

        $this->assertInstanceOf(CreateTransactionService::class, $service);
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

    public function testUsesDefaultExecuteFromParent(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $parentReflection = $reflection->getParentClass();
        
        $this->assertTrue($parentReflection->hasMethod('execute'));
        $this->assertFalse($reflection->hasMethod('execute') && $reflection->getMethod('execute')->getDeclaringClass() === $reflection);
    }

    public function testUsesDefaultGetServiceFromParent(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $parentReflection = $reflection->getParentClass();
        
        $this->assertTrue($parentReflection->hasMethod('getService'));
        $this->assertFalse($reflection->hasMethod('getService') && $reflection->getMethod('getService')->getDeclaringClass() === $reflection);
    }

    public function testGetServiceReturnsTransactions(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($this->service);

        $this->assertEquals('transactions', $result);
    }

    public function testIsSimpleExtensionOfAbstractTransactionsService(): void
    {
        $reflection = new \ReflectionClass($this->service);
        
        // Should not have any custom methods beyond what's inherited
        $ownMethods = array_filter(
            $reflection->getMethods(),
            fn($method) => $method->getDeclaringClass()->getName() === CreateTransactionService::class
        );

        $this->assertEmpty($ownMethods, 'CreateTransactionService should not declare any custom methods');
    }

    public function testClassHasNoCustomProperties(): void
    {
        $reflection = new \ReflectionClass($this->service);
        
        // Should not have any custom properties beyond what's inherited
        $ownProperties = array_filter(
            $reflection->getProperties(),
            fn($property) => $property->getDeclaringClass()->getName() === CreateTransactionService::class
        );

        $this->assertEmpty($ownProperties, 'CreateTransactionService should not declare any custom properties');
    }

    public function testInheritsAllParentFunctionality(): void
    {
        $parentReflection = new \ReflectionClass(\Rede\v2\Service\AbstractTransactionsService::class);
        $childReflection = new \ReflectionClass($this->service);
        
        $parentMethods = $parentReflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED);
        
        foreach ($parentMethods as $parentMethod) {
            if ($parentMethod->isConstructor()) {
                continue;
            }
            
            $this->assertTrue(
                $childReflection->hasMethod($parentMethod->getName()),
                "CreateTransactionService should inherit {$parentMethod->getName()} method"
            );
        }
    }

    public function testCanBeInstantiatedWithMinimalParameters(): void
    {
        $service = new CreateTransactionService($this->store);
        
        $this->assertInstanceOf(CreateTransactionService::class, $service);
        $this->assertInstanceOf(\Rede\v2\Service\AbstractTransactionsService::class, $service);
    }

    public function testImplementsCorrectNamespace(): void
    {
        $reflection = new \ReflectionClass($this->service);
        
        $this->assertEquals('Rede\v2\Service', $reflection->getNamespaceName());
    }

    public function testIsNotAbstract(): void
    {
        $reflection = new \ReflectionClass($this->service);
        
        $this->assertFalse($reflection->isAbstract());
    }

    public function testIsNotFinal(): void
    {
        $reflection = new \ReflectionClass($this->service);
        
        $this->assertFalse($reflection->isFinal());
    }

    public function testCanBeExtended(): void
    {
        $extendedService = new class($this->store) extends CreateTransactionService {
            public function customMethod(): string
            {
                return 'extended';
            }
        };
        
        $this->assertInstanceOf(CreateTransactionService::class, $extendedService);
        $this->assertEquals('extended', $extendedService->customMethod());
    }
}
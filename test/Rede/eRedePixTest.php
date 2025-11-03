<?php

namespace Rede;

use RuntimeException;
use PHPUnit\Framework\TestCase;

class eRedePixTest extends TestCase
{
    /**
     * @var Store|null
     */
    private ?Store $store = null;

    private static int $sequence = 1;

    public function testShouldCreateQrCodePix(): void
    {
        $transaction = (new Transaction(10.00, $this->generateReferenceNumber()))->pix(
            'pedido-pix-001'
        )->qrCode('2024-12-31T23:59:59-03:00');

        $transaction = $this->createERede()->create($transaction);
    }


    private function generateReferenceNumber(): string
    {
        return 'pedido' . (time() + eRedePixTest::$sequence++);
    }

    private function createERede(): eRede
    {
        if ($this->store === null || $this->logger === null) {
            throw new RuntimeException('Store cant be null');
        }

        return new eRede($this->store, $this->logger);
    }
}

<?php

namespace Rede\v2;

use Rede\Transaction;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use Rede\AbstractAuthentication;

class eRedePixTest extends TestCase
{
    /**
     * @var Store|null
     */
    private ?Store $store = null;
    private ?eRede $eRede = null;
    private ?AbstractAuthentication $authentication = null;

    private static int $sequence = 1;

    protected function setUp(): void
    {
        $filiation = getenv('REDE_PV');
        $token = getenv('REDE_TOKEN');

        $environment = Environment::sandbox();
        $this->store = new Store($filiation, $token, $environment);
        $this->eRede = $this->createERede();
        $this->authentication = $this->eRede->generateOAuthToken();
        $this->store->setAuth($this->authentication);
    }

    public function testShouldCreateQrCodePix(): void
    {
        $transaction = (new Transaction(10.00, $this->generateReferenceNumber()))->pix(
            'pedido-pix-001'
        )->qrCode('2025-11-04T23:59:59-03:00');

        $response = $this->eRede->create($transaction);

        $this->assertNotNull($response->getTid());
        $this->assertEquals('00', $response->getReturnCode());
        $this->assertNotNull($response->getQrCode());
        $this->assertNotNull($response->getQrCode()->getQrCodeImage());
        $this->assertNotNull($response->getQrCode()->getQrCodeData());
    }

    public function testShouldConsultATransactionByItsTID(): void
    {
        $pixTransaction = $this->createPixTransaction();
        $consultedTransaction = $this->eRede->get(
            $pixTransaction->getTid()
        );

        $authorization = $consultedTransaction->getAuthorization();

        if ($authorization === null) {
            throw new RuntimeException('Something happened with the authorized transaction');
        }

        $this->assertEquals($pixTransaction->getTid(), $authorization->getTid());
        $this->assertNotNull($authorization->getTxid());
    }

    public function testShouldConsultATransactionByReference(): void
    {
        $pixTransaction = $this->createPixTransaction();
        $consultedTransaction = $this->eRede->getByReference(
            $pixTransaction->getReference()
        );

        $authorization = $consultedTransaction->getAuthorization();

        if ($authorization === null) {
            throw new RuntimeException('Something happened with the authorized transaction');
        }

        $this->assertEquals($pixTransaction->getReference(), $authorization->getReference());
        $this->assertNotNull($authorization->getTxid());
    }

    public function testShouldCancelTransaction(): void
    {
        $this->markTestSkipped('Pix cancellation is not allowed in sandbox environment.');
        $pixTransaction = $this->createPixTransaction();

        $this->assertEquals('00', $pixTransaction->getReturnCode());

        //sleep(121); // wait a few seconds before canceling

        $canceledTransaction = $this->createERede()
            ->cancel((new Transaction(10.00))
                ->setTid($pixTransaction->getTid()));
        $this->assertEquals('359', $canceledTransaction->getReturnCode());
        $this->assertNotNull($canceledTransaction->getRefundId());
        $this->assertEquals($pixTransaction->getTid(), $canceledTransaction->getTid());
    }

    public function testShouldConsultTheTransactionRefunds(): void
    {
        $refundedTransactions = $this->createERede()->getRefunds('40012511031155163096');
        $this->assertCount(1, $refundedTransactions->getRefunds());
    }

    public function testShouldConsultTheTransactionRefundId(): void
    {
        $refundedTransactions = $this->createERede()->getRefundByRefundId('40012511031155163096', '47b33028-7e94-46a0-ba0e-0832b732370e');
        $this->assertCount(1, $refundedTransactions->getStatusHistory());
        $this->assertNotNull($refundedTransactions->getRefundId());
        $this->assertNotNull($refundedTransactions->getTxid());
        $this->assertNotNull($refundedTransactions->getTid());
    }


    private function generateReferenceNumber(): string
    {
        return 'pedido' . (time() + eRedePixTest::$sequence++);
    }

    private function createERede(): eRede
    {
        if ($this->store === null) {
            throw new RuntimeException('Store cant be null');
        }

        return new eRede($this->store);
    }

    private function createPixTransaction(): Transaction
    {
        return $this->createERede()->create(
            (new Transaction(10.00, $this->generateReferenceNumber()))->pix(
                'pedido-pix-001'
            )->qrCode('2025-11-04T23:59:59-03:00')
        );
    }
}

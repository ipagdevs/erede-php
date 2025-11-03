<?php

namespace Rede\v2\Service;

use Rede\Transaction;
use RuntimeException;
use InvalidArgumentException;
use Rede\Exception\RedeException;

class GetTransactionService extends AbstractTransactionsService
{
    /**
     * @var ?string
     */
    private ?string $reference = null;

    /**
     * @var bool
     */
    private bool $refund = false;

    /**
     * @var bool
     */
    private bool $refundByRefundId = false;

    /**
     * @return Transaction
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws RedeException
     */
    public function execute(): Transaction
    {
        return $this->sendRequest();
    }

    /**
     * @param string $reference
     *
     * @return $this
     */
    public function setReference(string $reference): static
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @param bool $refund
     *
     * @return $this
     */
    public function setRefund(bool $refund = true): static
    {
        $this->refund = $refund;

        return $this;
    }

    public function setRefundByRefundId(bool $refundByRefundId = true): static
    {
        $this->refundByRefundId = $refundByRefundId;

        return $this;
    }


    /**
     * @return string
     */
    protected function getService(): string
    {
        if ($this->reference !== null) {
            return sprintf('%s?reference=%s', parent::getService(), $this->reference);
        }

        if ($this->refund) {
            return sprintf('%s/%s/refunds', parent::getService(), $this->getTid());
        }

        if ($this->refundByRefundId) {
            return sprintf('%s/%s/refunds/%s', parent::getService(), $this->getTid(), $this->getRefundId());
        }

        return sprintf('%s/%s', parent::getService(), $this->getTid());
    }
}

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

    /**
     * @return string
     */
    protected function getService(): string
    {
        if ($this->reference !== null) {
            return sprintf('%s?reference=%s', parent::getService(), $this->reference);
        }

        if ($this->refund) {
            return sprintf('%s/%s/refunds', parent::getService(), $this->transaction->getTid());
        }

        return sprintf('%s/%s', parent::getService(), $this->transaction->getTid());
    }
}

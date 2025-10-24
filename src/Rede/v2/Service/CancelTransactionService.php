<?php

namespace Rede\v2\Service;

use RuntimeException;

class CancelTransactionService extends AbstractTransactionsService
{
    /**
     * @return string
     */
    protected function getService(): string
    {
        if ($this->transaction === null || !$this->transaction->getTid()) {
            throw new RuntimeException('Transaction was not defined yet');
        }

        return sprintf('%s/%s/refunds', parent::getService(), $this->transaction->getTid());
    }
}

<?php

namespace Rede\v2;

use Rede\Store;
use Rede\Transaction;
use Psr\Log\LoggerInterface;
use Rede\BasicAuthentication;
use Rede\AbstractAuthentication;
use Rede\CredentialsEnvironment;
use Rede\v2\Contracts\eRedeInterface;
use Rede\v2\Service\GetTransactionService;
use Rede\Service\OAuthAuthenticationService;
use Rede\v2\Service\CancelTransactionService;
use Rede\v2\Service\CreateTransactionService;
use Rede\v2\Service\CaptureTransactionService;

class eRede extends \Rede\eRede implements eRedeInterface
{
    /**
     * @var string|null
     */
    private ?string $platform = null;

    /**
     * @var string|null
     */
    private ?string $platformVersion = null;

    public function __construct(private readonly Store $store, private readonly ?LoggerInterface $logger = null)
    {
        parent::__construct($store, $logger);
    }

    public function generateOAuthToken(): AbstractAuthentication
    {
        $credentialsEnvironment = $this->store->getEnvironment()->getEndpoint('') === Environment::sandbox()->getEndpoint('')
            ? CredentialsEnvironment::sandbox()
            : CredentialsEnvironment::production();

        $authentication = new BasicAuthentication($this->store, $credentialsEnvironment);

        $service = new OAuthAuthenticationService($authentication, $this->logger);

        $service->withHeaders([
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Type: application/json; charset=utf8',
            'Accept: application/json',
        ]);

        return $service->execute([
            'grant_type' => 'client_credentials',
        ]);
    }

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     * @see    eRede::create()
     */
    public function authorize(Transaction $transaction): Transaction
    {
        return $this->create($transaction);
    }

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     */
    public function create(Transaction $transaction): Transaction
    {
        $service = new CreateTransactionService($this->store, $transaction, $this->logger);
        $service->platform($this->platform, $this->platformVersion);

        return $service->execute();
    }

    /**
     * @param string $platform
     * @param string $platformVersion
     *
     * @return $this
     */
    public function platform(string $platform, string $platformVersion): static
    {
        $this->platform = $platform;
        $this->platformVersion = $platformVersion;

        return $this;
    }

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     */
    public function cancel(Transaction $transaction): Transaction
    {
        $service = new CancelTransactionService($this->store, $transaction, $this->logger);
        $service->platform($this->platform, $this->platformVersion);

        return $service->execute();
    }

    /**
     * @param string $tid
     *
     * @return Transaction
     */
    public function get(string $tid): Transaction
    {
        $service = new GetTransactionService(store: $this->store, logger: $this->logger);
        $service->platform($this->platform, $this->platformVersion);
        $service->setTid($tid);

        return $service->execute();
    }

    /**
     * @param string $reference
     *
     * @return Transaction
     */
    public function getByReference(string $reference): Transaction
    {
        $service = new GetTransactionService(store: $this->store, logger: $this->logger);
        $service->platform($this->platform, $this->platformVersion);
        $service->setReference($reference);

        return $service->execute();
    }

    /**
     * @param string $tid
     *
     * @return Transaction
     */
    public function getRefunds(string $tid): Transaction
    {
        $service = new GetTransactionService(
            store: $this->store,
            logger: $this->logger
        );
        $service->platform($this->platform, $this->platformVersion);
        $service->setTid($tid);
        $service->setRefund();

        return $service->execute();
    }

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     */
    public function capture(Transaction $transaction): Transaction
    {
        $service = new CaptureTransactionService($this->store, $transaction, $this->logger);
        $service->platform($this->platform, $this->platformVersion);

        return $service->execute();
    }
}

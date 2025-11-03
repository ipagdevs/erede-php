<?php

namespace Rede\v2;

use Rede\Environment;
use Rede\AbstractAuthentication;

class Store extends \Rede\Store
{
    public function __construct(private string $filiation, private string $token, ?Environment $environment = null, private ?AbstractAuthentication $auth = null)
    {
        parent::__construct($filiation, $token, $environment);
    }

    /**
     * @return AbstractAuthentication|null
     */
    public function getAuth(): ?AbstractAuthentication
    {
        return $this->auth;
    }

    /**
     * @param AbstractAuthentication|null $auth
     *
     * @return $this
     */
    public function setAuth(?AbstractAuthentication $auth = null): static
    {
        $this->auth = $auth;
        return $this;
    }
}

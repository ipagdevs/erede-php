<?php

namespace Rede;

abstract class AbstractAuthentication
{
    /**
     * Which environment will this store used for?
     * @var CredentialsEnvironment
     */
    protected CredentialsEnvironment $environment;

    abstract public function getCredentials(): array;
    abstract public function toString(): string;

    public function __construct(?CredentialsEnvironment $environment = null)
    {
        $this->environment = $environment ?? CredentialsEnvironment::production();
    }

    /**
     * @return CredentialsEnvironment
     */
    public function getEnvironment(): CredentialsEnvironment
    {
        return $this->environment;
    }

    /**
     * @param CredentialsEnvironment $environment
     *
     * @return $this
     */
    public function setEnvironment(CredentialsEnvironment $environment): static
    {
        $this->environment = $environment;
        return $this;
    }

    public static function make(...$rest): AbstractAuthentication
    {
        return new static(...$rest);
    }
}

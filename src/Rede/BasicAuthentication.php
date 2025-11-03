<?php

namespace Rede;

class BasicAuthentication extends AbstractAuthentication
{
    public function __construct(private Store $store, ?CredentialsEnvironment $environment)
    {
        parent::__construct($environment);
    }

    public function getUsername(): string
    {
        return $this->store->getFiliation();
    }

    public function getPassword(): string
    {
        return $this->store->getToken();
    }

    public function setUsername(string $username): void
    {
        $this->store->setFiliation($username);
    }

    public function setPassword(string $password): void
    {
        $this->store->setToken($password);
    }

    public function getCredentials(): array
    {
        return [
            'username' => $this->store->getFiliation(),
            'password' => $this->store->getToken(),
        ];
    }

    public function toString(): string
    {
        return 'Basic ' . base64_encode(sprintf('%s:%s', $this->store->getFiliation(), $this->store->getToken()));
    }
}

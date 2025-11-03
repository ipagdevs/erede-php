<?php

namespace Rede;

class CredentialsEnvironment extends Environment
{
    public const PRODUCTION = 'https://api.userede.com.br/redelabs';
    public const SANDBOX = 'https://rl7-sandbox-api.useredecloud.com.br';
    public const VERSION = '';

    /**
     * @var string
     */
    private string $endpoint;

    /**
     * Creates an environment with its base url and version
     *
     * @param string $baseUrl
     */
    private function __construct(string $baseUrl)
    {
        $this->endpoint = sprintf('%s/%s', $baseUrl, self::VERSION);
    }

    public function getEndpoint(string $service): string
    {
        return $this->endpoint . $service;
    }

    public function getIp(): ?string
    {
        return parent::getIp();
    }

    public function getSessionId(): ?string
    {
        return parent::getSessionId();
    }

    /**
     * @return CredentialsEnvironment A preconfigured production environment
     */
    public static function production(): CredentialsEnvironment
    {
        return new CredentialsEnvironment(CredentialsEnvironment::PRODUCTION);
    }

    /**
     * @return CredentialsEnvironment A preconfigured sandbox environment
     */
    public static function sandbox(): CredentialsEnvironment
    {
        return new CredentialsEnvironment(CredentialsEnvironment::SANDBOX);
    }
}

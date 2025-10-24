<?php

namespace Rede\v2;

class Environment extends \Rede\Environment
{
    public const PRODUCTION = 'https://api.userede.com.br/erede';
    public const SANDBOX = 'https://sandbox-erede.useredecloud.com.br';
    public const VERSION = 'v2';

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
     * @return Environment A preconfigured production environment
     */
    public static function production(): self
    {
        return new self(self::PRODUCTION);
    }

    /**
     * @return Environment A preconfigured sandbox environment
     */
    public static function sandbox(): self
    {
        return new self(self::SANDBOX);
    }
}

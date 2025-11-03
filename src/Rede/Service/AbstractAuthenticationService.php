<?php

namespace Rede\Service;

use Psr\Log\LoggerInterface;
use Rede\AbstractAuthentication;

abstract class AbstractAuthenticationService
{
    private array $headers = [
        'Accept: application/json',
    ];

    public function __construct(private AbstractAuthentication $authentication, private ?LoggerInterface $logger = null)
    {
    }

    abstract protected function getService(): string;
    abstract public function execute(): AbstractAuthentication;
    abstract protected function parseResponse(string $response, int $statusCode): AbstractAuthentication;

    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function addHeader(string $header, string $value): self
    {
        $this->headers[] = "$header: $value";
        return $this;
    }

    protected function sendRequest(string $method, string $service, array $data = []): AbstractAuthentication
    {
        $body = http_build_query($data);

        $this->addHeader('Authorization', $this->authentication->toString());

        $curl = curl_init($this->authentication->getEnvironment()->getEndpoint($this->getService()));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);

        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $this->logger?->debug(
            trim(
                sprintf(
                    "Request Rede\n%s %s\n%s\n\n%s",
                    $method,
                    $this->authentication->getEnvironment()->getEndpoint($this->getService()),
                    implode("\n", $this->headers),
                    $method === 'POST' ? $body : ''
                )
            )
        );

        $response = curl_exec($curl);
        $httpInfo = curl_getinfo($curl);

        $this->logger?->debug(
            sprintf(
                "Response Rede\nStatus Code: %s\n\n%s",
                $httpInfo['http_code'],
                preg_replace_callback(
                    '/"access_token"\s*:\s*"([^"]+)"/i',
                    function ($m) {
                        $token = $m[1];
                        $len = mb_strlen($token);
                        $mask = '*****';
                        $start = 4;
                        $end = 4;
                        $left = mb_substr($token, 0, $start);
                        $right = mb_substr($token, $len - $end, $end);
                        return '"access_token":"' . $left . $mask . $right . '"';
                    },
                    $response
                )
            )
        );

        if (curl_errno($curl)) {
            throw new \RuntimeException(sprintf('Curl error[%s]: %s', curl_errno($curl), curl_error($curl)));
        }

        if (!is_string($response)) {
            throw new \RuntimeException('Error obtaining a response from the API');
        }

        curl_close($curl);

        return $this->parseResponse($response, $httpInfo['http_code']);
    }
}

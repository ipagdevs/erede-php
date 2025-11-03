<?php

namespace Rede\Service;

use Rede\BearerAuthentication;
use Rede\Exception\RedeException;

class OAuthAuthenticationService extends AbstractAuthenticationService
{
    protected function getService(): string
    {
        return 'oauth2/token';
    }

    public function execute(array $data = []): \Rede\AbstractAuthentication
    {
        return $this->sendRequest('POST', $this->getService(), $data);
    }

    protected function parseResponse(string $response, int $statusCode): \Rede\AbstractAuthentication
    {
        $previous = null;

        try {
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException(sprintf("unprocessed response to object JSON: %s", json_last_error_msg()));
            }
        } catch (\Exception $e) {
            $previous = $e;
        }

        if ($statusCode >= 400) {
            $errorCode = isset($data['error_code']) ? (int) $data['error_code'] : 0;
            $errorType = isset($data['error']) ? $data['error'] : 'unknown_error';
            $errorMessage = isset($data['error_description']) ? $data['error_description'] : 'Error on getting the content from the API';

            throw new RedeException(
                "[$errorType]: $errorMessage",
                $errorCode,
                $previous
            );
        }

        return BearerAuthentication::withCredentials($data);
    }
}

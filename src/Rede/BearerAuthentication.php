<?php

namespace Rede;

class BearerAuthentication extends AbstractAuthentication
{
    private ?int $expiresIn = null;
    private ?string $token = null;
    private string $type = 'Bearer';

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getExpiresIn(): ?int
    {
        return $this->expiresIn;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function setExpiresIn(?int $expiresIn): self
    {
        $this->expiresIn = $expiresIn;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getCredentials(): array
    {
        return [
            'type' => $this->type,
            'token' => $this->token,
            'expires_in' => $this->expiresIn,
        ];
    }

    public static function withCredentials(array $credentials): self
    {
        $instance = new self();

        if (isset($credentials['token_type'])) {
            $instance->type = $credentials['token_type'];
        }

        if (isset($credentials['access_token'])) {
            $instance->token = $credentials['access_token'];
        }

        if (isset($credentials['expires_in'])) {
            $instance->expiresIn = $credentials['expires_in'];
        }

        return $instance;
    }

    public function toString(): string
    {
        return sprintf('%s %s', $this->type, $this->token);
    }
}

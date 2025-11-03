<?php

namespace Rede;

class QrCode implements RedeSerializable
{
    use SerializeTrait;

    /**
     * @var string|null
     */
    private ?string $dateTimeExpiration = null;

    public function getDateTimeExpiration(): ?string
    {
        return $this->dateTimeExpiration;
    }

    public function setDateTimeExpiration(string $dateTimeExpiration): static
    {
        $this->dateTimeExpiration = $dateTimeExpiration;
        return $this;
    }
}

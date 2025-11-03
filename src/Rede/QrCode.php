<?php

namespace Rede;

use DateTime;

class QrCode implements RedeSerializable
{
    use SerializeTrait, CreateTrait;

    /**
     * @var string|null
     */
    private ?string $dateTimeExpiration = null;
    /**
     * @var string|null
     */
    private ?string $qrCodeImage = null;
    /**
     * @var string|null
     */
    private ?string $qrCodeData = null;
    /**
     * @var string|null
     */
    private ?DateTime $expirationQrCode = null;

    public function getDateTimeExpiration(): ?string
    {
        return $this->dateTimeExpiration;
    }

    public function setDateTimeExpiration(string $dateTimeExpiration): static
    {
        $this->dateTimeExpiration = $dateTimeExpiration;
        return $this;
    }

    public function getQrCodeImage(): ?string
    {
        return $this->qrCodeImage;
    }

    public function setQrCodeImage(?string $qrCodeImage): static
    {
        $this->qrCodeImage = $qrCodeImage;
        return $this;
    }

    public function getQrCodeData(): ?string
    {
        return $this->qrCodeData;
    }

    public function setQrCodeData(?string $qrCodeData): static
    {
        $this->qrCodeData = $qrCodeData;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getExpirationQrCode(): ?DateTime
    {
        return $this->expirationQrCode;
    }

    /**
     * @param DateTime|null $expirationQrCode
     * @return $this
     */
    public function setExpirationQrCode(?DateTime $expirationQrCode): static
    {
        $this->expirationQrCode = $expirationQrCode;
        return $this;
    }
}

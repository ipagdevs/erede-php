<?php

namespace Rede;

use DateTime;

class StatusHistory implements RedeSerializable
{
    use SerializeTrait, CreateTrait;

    private ?DateTime $dateTime = null;
    private ?string $status = null;

    public function getDateTime(): ?DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime(?DateTime $dateTime): static
    {
        $this->dateTime = $dateTime;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }
}

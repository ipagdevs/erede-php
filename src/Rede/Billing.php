<?php

namespace Rede;

class Billing implements RedeSerializable
{
    use SerializeTrait, CreateTrait;

    /**
     * @param string|null $address max length 128
     * @param string|null $city max length 64
     * @param string|null $state max length 64
     * @param string|null $country max length 64
     * @param string|null $postalcode max length 9
     * @param string|null $emailAddress max length 128
     * @param string|null $phoneNumber max length 32
     */
    public function __construct(
        private ?string $address = null,
        private ?string $city = null,
        private ?string $state = null,
        private ?string $country = null,
        private ?string $postalcode = null,
        private ?string $emailAddress = null,
        private ?string $phoneNumber = null,
    ) {}

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getPostalcode(): ?string
    {
        return $this->postalcode;
    }

    public function setPostalcode(?string $postalcode): static
    {
        $this->postalcode = $postalcode;
        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): static
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }
}

<?php

namespace Rede;

class Brand
{
    use CreateTrait;

    /**
     * @var string|null
     */
    private ?string $name = null;

    /**
     * @var string|null
     */
    private ?string $returnCode = null;
    /**
     * @var string|null
     */
    private ?string $returnMessage = null;

    /**
     * @var string|null
     */
    private ?string $brandTid = null;

    /**
     * @var string|null
     */
    private ?string $authorizationCode = null;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Brand
     */
    public function setName(?string $name): Brand
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReturnCode(): ?string
    {
        return $this->returnCode;
    }

    /**
     * @param string|null $returnCode
     * @return Brand
     */
    public function setReturnCode(?string $returnCode): Brand
    {
        $this->returnCode = $returnCode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReturnMessage(): ?string
    {
        return $this->returnMessage;
    }

    /**
     * @param string|null $returnMessage
     * @return Brand
     */
    public function setReturnMessage(?string $returnMessage): Brand
    {
        $this->returnMessage = $returnMessage;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBrandTid(): ?string
    {
        return $this->brandTid;
    }

    /**
     * @param string|null $brandTid
     * @return Brand
     */
    public function setBrandTid(?string $brandTid): Brand
    {
        $this->brandTid = $brandTid;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthorizationCode(): ?string
    {
        return $this->authorizationCode;
    }

    /**
     * @param string|null $authorizationCode
     * @return Brand
     */
    public function setAuthorizationCode(?string $authorizationCode): Brand
    {
        $this->authorizationCode = $authorizationCode;
        return $this;
    }
}

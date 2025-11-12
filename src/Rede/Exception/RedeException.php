<?php

namespace Rede\Exception;

use Rede\Brand;
use RuntimeException;

class RedeException extends RuntimeException
{
    protected ?Brand $brand = null;

    /**
     * @return Brand|null
     */
    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    /**
     * @param Brand|null $brand
     * @return $this
     */
    public function setBrand(?Brand $brand): static
    {
        $this->brand = $brand;
        return $this;
    }
}

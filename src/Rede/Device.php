<?php

namespace Rede;

class Device implements RedeSerializable
{
    use CreateTrait;
    use SerializeTrait;

    /**
     * @param string|int|null $colorDepth
     * @param string|null     $deviceType3ds
     * @param bool|null       $javaEnabled
     * @param string          $language
     * @param int|null        $screenHeight
     * @param int|null        $screenWidth
     * @param int|null        $timeZoneOffset
     */
    public function __construct(
        private string|int|null $colorDepth = null,
        private ?string $deviceType3ds = null,
        private ?bool $javaEnabled = null,
        private string $language = 'BR',
        private ?int $screenHeight = null,
        private ?int $screenWidth = null,
        private ?int $timeZoneOffset = 3,
    ) {}

    /**
     * @return string|null
     */
    public function getcolorDepth(): ?string
    {
        return $this->colorDepth;
    }

    /**
     * @param string $colorDepth
     * @return $this
     */
    public function setcolorDepth(string $colorDepth): static
    {
        $this->colorDepth = $colorDepth;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getdeviceType3ds(): ?string
    {
        return $this->deviceType3ds;
    }

    /**
     * @param string $deviceType3ds
     * @return $this
     */
    public function setdeviceType3ds(string $deviceType3ds): static
    {
        $this->deviceType3ds = $deviceType3ds;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getjavaEnabled(): ?bool
    {
        return $this->javaEnabled;
    }

    /**
     * @param bool $javaEnabled
     * @return $this
     */
    public function setjavaEnabled(bool $javaEnabled = true): static
    {
        $this->javaEnabled = $javaEnabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getlanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setlanguage(string $language): static
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getscreenHeight(): ?int
    {
        return $this->screenHeight;
    }

    /**
     * @param int $screenHeight
     * @return $this
     */
    public function setscreenHeight(int $screenHeight): static
    {
        $this->screenHeight = $screenHeight;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getscreenWidth(): ?int
    {
        return $this->screenWidth;
    }

    /**
     * @param int $screenWidth
     * @return $this
     */
    public function setscreenWidth(int $screenWidth): static
    {
        $this->screenWidth = $screenWidth;
        return $this;
    }

    /**
     * @return int|null
     */
    public function gettimeZoneOffset(): ?int
    {
        return $this->timeZoneOffset;
    }

    /**
     * @param int $timeZoneOffset
     * @return $this
     */
    public function settimeZoneOffset(int $timeZoneOffset): static
    {
        $this->timeZoneOffset = $timeZoneOffset;
        return $this;
    }
}

<?php
/**
 * Created by PhpStorm.
 * Date: 18.01.19
 */

namespace MBH\Bundle\OnlineBundle\Lib\Site\image;


class ImageData implements \JsonSerializable
{
    /**
     * @var bool
     */
    private $isMain = false;

    /**
     * @var int|null
     */
    private $width;

    /**
     * @var int|null
     */
    private $height;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $smallUrl;

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->isMain;
    }

    /**
     * @param bool $isMain
     */
    public function setIsMain(bool $isMain): self
    {
        $this->isMain = $isMain;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param $width
     */
    public function setWidth($width): self
    {
        $this->width = $width ? (int)$width : null;

        return $this;
    }

    /**
     * @param $height
     */
    public function setHeight($height): self
    {
        $this->height = $height ? (int)$height : null;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }


    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getSmallUrl(): string
    {
        return $this->smallUrl;
    }

    /**
     * @param string $smallUrl
     */
    public function setSmallUrl(string $smallUrl): self
    {
        $this->smallUrl = $smallUrl;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'isMain'   => $this->isMain(),
            'url'      => $this->getUrl(),
            'smallUrl' => $this->getSmallUrl(),
            'width'    => $this->getWidth(),
            'height'   => $this->getHeight(),
        ];
    }

}
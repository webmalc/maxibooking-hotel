<?php
/**
 * Created by PhpStorm.
 * Date: 31.05.18
 */

namespace MBH\Bundle\OnlineBundle\Document;


trait DecorationTrait
{
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Choice(callback = "getThemes")
     */
    protected $theme;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $css;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\GreaterThan(0)
     */
    private $frameWidth = 300;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\GreaterThan(0)
     */
    private $frameHeight = 400;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     * @Assert\Type(type="bool")
     */
    private $isFullWidth = false;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Type(type="string")
     * @Assert\Length(
     *     max=65536
     * )
     */
    private $formTemplate;

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Collection
     * @Assert\Choice(callback = "getCssLibrariesList", multiple = true)
     */
    private $cssLibraries;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    private $isHorizontal = false;

    /**
     * @return bool
     */
    public function isHorizontal(): ?bool
    {
        return $this->isHorizontal;
    }

    /**
     * @param bool $isHorizontal
     * @return static
     */
    public function setIsHorizontal(bool $isHorizontal): self
    {
        $this->isHorizontal = $isHorizontal;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFullWidth(): bool
    {
        return $this->isFullWidth;
    }

    /**
     * @param bool $isFullWidth
     * @return static
     */
    public function setIsFullWidth(bool $isFullWidth): self
    {
        $this->isFullWidth = $isFullWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrameWidth(): int
    {
        return $this->frameWidth;
    }

    /**
     * @param int $frameWidth
     * @return static
     */
    public function setFrameWidth(int $frameWidth): self
    {
        $this->frameWidth = $frameWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrameHeight(): int
    {
        return $this->frameHeight;
    }

    /**
     * @param int $frameHeight
     * @return static
     */
    public function setFrameHeight(int $frameHeight): self
    {
        $this->frameHeight = $frameHeight;

        return $this;
    }

    /**
     * @return string
     */
    public function getCss(): ?string
    {
        return $this->css;
    }

    /**
     * @param string $css
     * @return static
     */
    public function setCss(?string $css = null): self
    {
        $this->css = $css;
        return $this;
    }

    /**
     * @return string
     */
    public function getTheme(): ?string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     * @return static
     */
    public function setTheme(string $theme = null): self
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFormTemplate(): ?string
    {
        return $this->formTemplate;
    }

    /**
     * @param string $formTemplate
     * @return static
     */
    public function setFormTemplate(string $formTemplate = null): self
    {
        $this->formTemplate = $formTemplate;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getCssLibraries(): ?array
    {
        return $this->cssLibraries;
    }

    /**
     * @param array $cssLibraries
     * @return static
     */
    public function setCssLibraries(array $cssLibraries = null): self
    {
        $this->cssLibraries = $cssLibraries;

        return $this;
    }
}

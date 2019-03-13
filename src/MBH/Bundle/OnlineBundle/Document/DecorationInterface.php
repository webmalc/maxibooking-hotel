<?php
/**
 * Created by PhpStorm.
 * Date: 31.05.18
 */

namespace MBH\Bundle\OnlineBundle\Document;


use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;

interface DecorationInterface
{
    /**
     * @return bool|null
     */
    public function isHorizontal(): ?bool;

    /**
     * @param bool $isHorizontal
     */
    public function setIsHorizontal(bool $isHorizontal);

    /**
     * @return bool
     */
    public function isFullWidth(): bool;

    /**
     * @param bool $isFullWidth
     * @return FormConfig
     */
    public function setIsFullWidth(bool $isFullWidth);

    /**
     * @return int
     */
    public function getFrameWidth(): int;

    /**
     * @param int $frameWidth
     */
    public function setFrameWidth(int $frameWidth);

    /**
     * @return int
     */
    public function getFrameHeight(): int;

    /**
     * @param int $frameHeight
     */
    public function setFrameHeight(int $frameHeight);

    /**
     * @return null|string
     */
    public function getCss(): ?string;

    /**
     * @param string $css
     */
    public function setCss(string $css = null);

    /**
     * @return string
     */
    public function getTheme(): ?string;

    /**
     * @param string $theme
     */
    public function setTheme(string $theme = null);

    /**
     * @return string
     */
    public function getFormTemplate(): ?string;

    /**
     * @param string $formTemplate
     */
    public function setFormTemplate(string $formTemplate = null);

    /**
     * @return array|null
     */
    public function getCssLibraries(): ?array;

    /**
     * @param array $cssLibraries
     */
    public function setCssLibraries(array $cssLibraries = null);
}
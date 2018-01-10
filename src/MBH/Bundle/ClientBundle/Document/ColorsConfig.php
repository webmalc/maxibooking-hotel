<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 26.06.17
 * Time: 14:28
 */

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="ColorsConfig", repositoryClass="MBH\Bundle\ClientBundle\Document\ColorsConfigRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class ColorsConfig extends Base
{
    const DEFAULT_SUCCESS_COLOR = '#8bc34a';
    const DEFAULT_SUCCESS_ADDITIONAL_COLOR = '#00a65a';
    const DEFAULT_DANGER_COLOR = '#ff9e80';
    const DEFAULT_DANGER_ADDITIONAL_COLOR = '#dd4b39';
    const DEFAULT_WARNING_COLOR = '#fdd835';
    const DEFAULT_WARNING_ADDITIONAL_COLOR = '#fd9c12';
    const LEFT_ROOMS_POSITIVE = 'yellowgreen';
    const LEFT_ROOMS_NEGATIVE = 'rgba(221, 75, 57, 0.6)';
    const LEFT_ROOMS_ZERO = 'rgba(243, 156, 18, 0.66)';
    const UNPLACED = 'rgba(193, 232, 42, 0.55)';

    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;

    /**
     * Hook softdeleteable behavior
     * deletedAt field
     */
    use SoftDeleteableDocument;

    /**
     * Hook blameable behavior
     * createdBy&updatedBy fields
     */
    use BlameableDocument;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.colors_config.min_hex_code",
     *      max=7,
     *      maxMessage="validator.colors_config.max_hex_code"
     * )
     */
    private $successColor = self::DEFAULT_SUCCESS_COLOR;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.colors_config.min_hex_code",
     *      max=7,
     *      maxMessage="validator.colors_config.max_hex_code"
     * )
     */
    private $successAdditionalColor = self::DEFAULT_SUCCESS_ADDITIONAL_COLOR;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.colors_config.min_hex_code",
     *      max=7,
     *      maxMessage="validator.colors_config.max_hex_code"
     * )
     */
    private $warningColor = self::DEFAULT_WARNING_COLOR;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.colors_config.min_hex_code",
     *      max=7,
     *      maxMessage="validator.colors_config.max_hex_code"
     * )
     */
    private $warningAdditionalColor = self::DEFAULT_WARNING_ADDITIONAL_COLOR;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.colors_config.min_hex_code",
     *      max=7,
     *      maxMessage="validator.colors_config.max_hex_code"
     * )
     */
    private $dangerColor = self::DEFAULT_DANGER_COLOR;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.colors_config.min_hex_code",
     *      max=7,
     *      maxMessage="validator.colors_config.max_hex_code"
     * )
     */
    private $dangerAdditionalColor = self::DEFAULT_DANGER_ADDITIONAL_COLOR;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.colors_config.min_hex_code",
     *      max=22,
     *      maxMessage="validator.colors_config.max_color_length"
     * )
     */
    private $leftRoomsPositiveColor = self::LEFT_ROOMS_POSITIVE;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.colors_config.min_hex_code",
     *      max=22,
     *      maxMessage="validator.colors_config.max_color_length"
     * )
     */
    private $leftRoomsNegativeColor = self::LEFT_ROOMS_NEGATIVE;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.colors_config.min_hex_code",
     *      max=22,
     *      maxMessage="validator.colors_config.max_color_length"
     * )
     */
    private $leftRoomsZeroColor = self::LEFT_ROOMS_ZERO;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.colors_config.min_hex_code",
     *      max=22,
     *      maxMessage="validator.colors_config.max_color_length"
     * )
     */
    private $unplacedColor = self::UNPLACED;

    /**
     * @return string
     */
    public function getSuccessAdditionalColor(): string
    {
        return $this->successAdditionalColor;
    }

    /**
     * @param string $successAdditionalColor
     * @return ColorsConfig
     */
    public function setSuccessAdditionalColor(string $successAdditionalColor): ColorsConfig
    {
        $this->successAdditionalColor = $successAdditionalColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getWarningAdditionalColor(): string
    {
        return $this->warningAdditionalColor;
    }

    /**
     * @param string $warningAdditionalColor
     * @return ColorsConfig
     */
    public function setWarningAdditionalColor(string $warningAdditionalColor): ColorsConfig
    {
        $this->warningAdditionalColor = $warningAdditionalColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getDangerAdditionalColor(): string
    {
        return $this->dangerAdditionalColor;
    }

    /**
     * @param string $dangerAdditionalColor
     * @return ColorsConfig
     */
    public function setDangerAdditionalColor(string $dangerAdditionalColor): ColorsConfig
    {
        $this->dangerAdditionalColor = $dangerAdditionalColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnplacedColor(): ?string
    {
        return $this->unplacedColor;
    }

    /**
     * @param string $unplacedColor
     * @return ColorsConfig
     */
    public function setUnplacedColor(string $unplacedColor): ColorsConfig
    {
        $this->unplacedColor = $unplacedColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getLeftRoomsPositiveColor(): ?string
    {
        return $this->leftRoomsPositiveColor;
    }

    /**
     * @param string $leftRoomsPositiveColor
     * @return ColorsConfig
     */
    public function setLeftRoomsPositiveColor(string $leftRoomsPositiveColor): ColorsConfig
    {
        $this->leftRoomsPositiveColor = $leftRoomsPositiveColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getLeftRoomsNegativeColor(): ?string
    {
        return $this->leftRoomsNegativeColor;
    }

    /**
     * @param string $leftRoomsNegativeColor
     * @return ColorsConfig
     */
    public function setLeftRoomsNegativeColor(string $leftRoomsNegativeColor): ColorsConfig
    {
        $this->leftRoomsNegativeColor = $leftRoomsNegativeColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getLeftRoomsZeroColor(): ?string
    {
        return $this->leftRoomsZeroColor;
    }

    /**
     * @param string $leftRoomsZeroColor
     * @return ColorsConfig
     */
    public function setLeftRoomsZeroColor(string $leftRoomsZeroColor): ColorsConfig
    {
        $this->leftRoomsZeroColor = $leftRoomsZeroColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuccessColor(): ?string
    {
        return $this->successColor;
    }

    /**
     * @param string $successColor
     * @return ColorsConfig
     */
    public function setSuccessColor(string $successColor): ColorsConfig
    {
        $this->successColor = $successColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getWarningColor(): ?string
    {
        return $this->warningColor;
    }

    /**
     * @param string $warningColor
     * @return ColorsConfig
     */
    public function setWarningColor(string $warningColor): ColorsConfig
    {
        $this->warningColor = $warningColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getDangerColor(): ?string
    {
        return $this->dangerColor;
    }

    /**
     * @param string $dangerColor
     * @return ColorsConfig
     */
    public function setDangerColor(string $dangerColor): ColorsConfig
    {
        $this->dangerColor = $dangerColor;

        return $this;
    }

    public function __toArray()
    {
        return [
            'success' => $this->getSuccessColor(),
            'success_add' => $this->getSuccessAdditionalColor(),
            'danger' => $this->getDangerColor(),
            'danger_add' => $this->getDangerAdditionalColor(),
            'warning' => $this->getWarningColor(),
            'warning_add' => $this->getWarningAdditionalColor(),
            'leftRoomsPositive' => $this->getLeftRoomsPositiveColor(),
            'leftRoomsNegative' => $this->getLeftRoomsNegativeColor(),
            'leftRoomsZero' => $this->getLeftRoomsZeroColor(),
            'unplaced' => $this->getUnplacedColor()
        ];
    }
}
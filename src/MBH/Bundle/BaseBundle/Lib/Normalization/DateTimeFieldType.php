<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\BaseBundle\Service\Utils;

class DateTimeFieldType implements NormalizableInterface
{
    private $format;

    public function __construct(string $format = MBHSerializer::DATE_FORMAT)
    {
        $this->format = $format;
    }

    /**
     * @param \DateTime $dateTime
     * @param array $options
     * @return string
     * @throws NormalizationException
     */
    public function normalize($dateTime, array $options = [])
    {
        if (!$dateTime instanceof \DateTime) {
            throw new NormalizationException('Can not normalize '
                . Utils::getStringValueOrType($dateTime)
                . ' because it\'s not an instance of DateTime');
        }

        return $dateTime->format($this->format);
    }

    /**
     * @param $dateTimeString
     * @param array $options
     * @return bool|\DateTime
     * @throws NormalizationException
     */
    public function denormalize($dateTimeString, array $options = [])
    {
        if ($this->format === MBHSerializer::DATE_FORMAT) {
            $denormalizationResult = \DateTime::createFromFormat($this->format . ' H', $dateTimeString . ' 00');
        } else {
            $denormalizationResult = \DateTime::createFromFormat($this->format, $dateTimeString);
        }

        if ($denormalizationResult instanceof \DateTime) {
            return $denormalizationResult;
        }

        throw new NormalizationException('Can not denormalize ' . $dateTimeString . ' to datetime by format "' . $this->format . '"');
    }
}
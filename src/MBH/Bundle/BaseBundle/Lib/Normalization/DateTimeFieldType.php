<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class DateTimeFieldType implements NormalizableInterface
{
    private $format;
    
    public function __construct(string $format = 'd.m.Y') {
        $this->format = $format;
    }

    /**
     * @param \DateTime $dateTime
     * @param array $options
     * @return string
     */
    public function normalize($dateTime, array $options)
    {
        if (!$dateTime instanceof \DateTime) {
            throw new InvalidArgumentException('Can not normalize value because it\'s not instance of DateTime');
        }

        return $dateTime->format($this->format);
    }

    /**
     * @param $dateTimeString
     * @param array $options
     * @return bool|\DateTime
     */
    public function denormalize($dateTimeString, array $options)
    {
        $denormalizationResult = \DateTime::createFromFormat($this->format, $dateTimeString);
        if ($denormalizationResult instanceof \DateTime) {
            return $denormalizationResult;
        }

        throw new InvalidArgumentException('Can not denormalize ' . $dateTimeString . ' to datetime by format "' . $this->format . '"');
    }
}
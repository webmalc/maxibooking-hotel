<?php

namespace MBH\Bundle\BaseBundle\Form\Extension;

use Symfony\Component\Form\Extension\Core\DataTransformer\BaseDateTimeTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms between a normalized time and a localized time string
 */
class DateTimeToLocalizedStringTransformer extends BaseDateTimeTransformer
{
    /**
     * @param null $inputTimezone
     * @param null $outputTimezone
     */
    public function __construct($inputTimezone = null, $outputTimezone = null)
    {
        parent::__construct($inputTimezone, $outputTimezone);
    }

    /**
     * @param mixed $dateTime
     * @return string
     */
    public function transform($dateTime)
    {
        if (null === $dateTime) {
            return '';
        }

        if (!$dateTime instanceof \DateTime) {
            throw new TransformationFailedException('Expected a \DateTime.');
        }

        return $dateTime->format('d.m.Y');
    }

    /**
     * @param mixed $value
     * @return \DateTime|void
     */
    public function reverseTransform($value)
    {
        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        if ('' === $value) {
            return;
        }

        $dateTime = \DateTime::createFromFormat('d.m.Y H:i:s', $value . ' 00:00:00', new \DateTimeZone($this->inputTimezone));

        if (!$dateTime instanceof \DateTime) {
            throw new TransformationFailedException();
        }

        return $dateTime;
    }
}

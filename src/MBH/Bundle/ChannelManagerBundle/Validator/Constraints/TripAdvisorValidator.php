<?php

namespace MBH\Bundle\ChannelManagerBundle\Validator\Constraints;

use MBH\Bundle\ChannelManagerBundle\Services\ChannelManagerHelper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TripAdvisorValidator extends ConstraintValidator
{
    private $translator;
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate($document, Constraint $constraint)
    {
        if ($document instanceof Tariff) {
            if ($this->isTripAdvisorConfigEnabled($document->getHotel())) {
                $unfilledFields = ChannelManagerHelper::getTariffRequiredUnfilledFields($document);
            }
        } elseif ($document instanceof RoomType) {
            if ($this->isTripAdvisorConfigEnabled($document->getHotel())) {
                $unfilledFields = ChannelManagerHelper::getRoomTypeRequiredUnfilledFields($document);
            }
        } elseif ($document instanceof Hotel) {
            if ($this->isTripAdvisorConfigEnabled($document)) {
                $unfilledFields = ChannelManagerHelper::getHotelUnfilledRequiredFields($document);
            }
        }
        if (count($unfilledFields)) {
            foreach ($unfilledFields as $unfilledFieldName) {
                $this->context->buildViolation('trip_advisor_validator.violation_message_template')
                    ->setParameter('%field%', $this->translator->trans($unfilledFieldName))
                    ->atPath('currencyDefaultRatio')
                    ->addViolation();
            }
        }

        return true;
    }

    private function isTripAdvisorConfigEnabled(Hotel $hotel)
    {
        $config = $hotel->getTripAdvisorConfig();

        return is_null($config) || $config->getIsEnabled();
    }
}
<?php

namespace MBH\Bundle\ChannelManagerBundle\Validator\Constraints;

use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorRoomType;
use MBH\Bundle\ChannelManagerBundle\Services\ChannelManagerHelper;
use MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor\TripAdvisorHelper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorTariff;

class TripAdvisorValidator extends ConstraintValidator
{
    private $translator;
    private $confirmationUrl;
    private $tripAdvisorHelper;

    public function __construct(TranslatorInterface $translator, $confirmationUrl, TripAdvisorHelper $tripAdvisorHelper)
    {
        $this->translator = $translator;
        $this->confirmationUrl = $confirmationUrl;
        $this->tripAdvisorHelper = $tripAdvisorHelper;
    }

    public function validate($document, Constraint $constraint)
    {
        if ($document instanceof Tariff) {
            if ($this->isTripAdvisorConfigEnabled($document->getHotel())) {
                foreach ($document->getHotel()->getTripAdvisorConfig()->getTariffs() as $tariff) {
                    /** @var TripAdvisorTariff $tariff */
                    if ($tariff->getTariff() === $document && $tariff->getIsEnabled()) {
                        $unfilledFields = $this->tripAdvisorHelper->getTariffRequiredUnfilledFields($document);
                    }
                }
            }
        } elseif ($document instanceof RoomType) {
            if ($this->isTripAdvisorConfigEnabled($document->getHotel())) {
                foreach ($document->getHotel()->getTripAdvisorConfig()->getRooms() as $room) {
                    /** @var TripAdvisorRoomType $room */
                    if ($room->getRoomType() === $document && $room->getIsEnabled()) {
                        $unfilledFields = $this->tripAdvisorHelper->getRoomTypeRequiredUnfilledFields($document);
                    }
                }
            }
        } elseif ($document instanceof Hotel) {
            if ($this->isTripAdvisorConfigEnabled($document)) {
                $unfilledFields = $this->tripAdvisorHelper->getHotelUnfilledRequiredFields($document, $this->confirmationUrl);
            }
        }
        if (isset($unfilledFields) && count($unfilledFields)) {
            foreach ($unfilledFields as $unfilledFieldName) {
                $this->context->buildViolation('trip_advisor_validator.violation_message_template')
                    ->setParameter('%field%', $this->translator->trans($unfilledFieldName))
                    ->addViolation();
            }
        }

        return true;
    }

    private function isTripAdvisorConfigEnabled(Hotel $hotel)
    {
        $config = $hotel->getTripAdvisorConfig();

        return !is_null($config) && $config->getIsEnabled();
    }
}
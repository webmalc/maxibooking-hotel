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

    public function __construct(TranslatorInterface $translator, $confirmationUrl)
    {
        $this->translator = $translator;
        $this->confirmationUrl = $confirmationUrl;
    }

    public function validate($document, Constraint $constraint)
    {
        if ($document instanceof Tariff) {
            foreach ($document->getHotel()->getTripAdvisorConfig()->getTariffs() as $tariff) {
                /** @var TripAdvisorTariff $tariff */
                if ($tariff->getTariff() == $document && $tariff->getIsEnabled()) {
                    if ($this->isTripAdvisorConfigEnabled($document->getHotel())) {
                        $unfilledFields = TripAdvisorHelper::getTariffRequiredUnfilledFields($document);
                    }
                }
            }
        } elseif ($document instanceof RoomType) {
            $isRoomSync = false;
            foreach ($document->getHotel()->getTripAdvisorConfig()->getRooms() as $room) {
                /** @var TripAdvisorRoomType $room */
                if ($room->getRoomType() == $document && $room->getIsEnabled()) {
                    $isRoomSync = true;
                }
            }
            if ($isRoomSync) {
                if ($this->isTripAdvisorConfigEnabled($document->getHotel())) {
                    $unfilledFields = TripAdvisorHelper::getRoomTypeRequiredUnfilledFields($document);
                }
            }
        } elseif ($document instanceof Hotel) {
            if ($this->isTripAdvisorConfigEnabled($document)) {
                $unfilledFields = TripAdvisorHelper::getHotelUnfilledRequiredFields($document, $this->confirmationUrl);
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
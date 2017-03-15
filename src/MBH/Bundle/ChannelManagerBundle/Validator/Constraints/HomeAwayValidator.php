<?php

namespace MBH\Bundle\ChannelManagerBundle\Validator\Constraints;


use MBH\Bundle\ChannelManagerBundle\Services\HomeAway\HomeAway;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HomeAwayValidator extends ConstraintValidator
{
    private $homeAwayService;

    public function __construct(HomeAway $homeAwayService)
    {
        $this->homeAwayService = $homeAwayService;
    }

    public function validate($document, Constraint $constraint)
    {
        if ($document instanceof Hotel) {
            if ($this->isHomeAwayConfigEnable($document)) {
                $errorMessage = $this->homeAwayService->getHotelRequiredDataMessage($document);
            }
        } elseif ($document instanceof RoomType) {
            if ($this->isHomeAwayConfigEnable($document->getHotel())) {
                $errorMessage = $this->homeAwayService->getRoomTypeRequiredDataMessage($document);
            }
        }

        if (!empty($errorMessage)) {
            $this->context
                ->buildViolation($errorMessage)->addViolation();
        }

        return true;
    }

    private function isHomeAwayConfigEnable(Hotel $hotel)
    {
        $config = $hotel->getTripAdvisorConfig();

        return is_null($config) || $config->getIsEnabled();
    }
}
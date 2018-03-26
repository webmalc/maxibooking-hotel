<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use Doctrine\ODM\MongoDB\DocumentManager;
use GuzzleHttp\Client;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\ServiceData;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffService;
use Symfony\Component\Validator\ConstraintViolation;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use Symfony\Component\Translation\TranslatorInterface;
use MBH\Bundle\CashBundle\Document\CardType;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TripAdvisorHelper
{
    /** @var  SearchFactory $search */
    private $search;
    private $translator;
    private $validator;
    /** @var  TripAdvisorResponseCompiler $responseFormatter */
    private $responseFormatter;
    private $mbhsKey;
    private $dm;

    const TRIP_ADVISOR_CONFIRMATION_URL = 'http://example.com';

    public function __construct(
        SearchFactory $search,
        TranslatorInterface $translator,
        ValidatorInterface $validator,
        TripAdvisorResponseCompiler $responseFormatter,
        $mbhsKey,
        DocumentManager $dm
    ) {
        $this->search = $search;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->responseFormatter = $responseFormatter;
        $this->mbhsKey = $mbhsKey;
        $this->dm = $dm;
    }

    /**
     * Проверяет отель на заполнение данных и возвращает названия незаполненных полей
     * @param Hotel $hotel
     * @param $confirmationUrl
     * @return array
     */
    public function getHotelUnfilledRequiredFields(Hotel $hotel, $confirmationUrl)
    {
        $requiredHotelData = [];
        $hotelContactInformation = $hotel->getContactInformation();

        !empty($hotel->getInternationalStreetName()) ?: $requiredHotelData[] = 'form.hotelExtendedType.international_street_name.help';
        !empty($hotel->getRegionId()) ?: $requiredHotelData[] = 'form.hotelExtendedType.region';
        !empty($hotel->getCountryTld()) ?: $requiredHotelData[] = 'form.hotelExtendedType.country';
        !empty($hotel->getCityId()) ?: $requiredHotelData[] = 'form.hotelExtendedType.city';
        if (empty($hotelContactInformation)) {
            $requiredHotelData[] = 'form.hotel_contact_information.contact_info.group';
        } else {
            !empty($hotelContactInformation->getEmail()) ?: $requiredHotelData[] = 'form.contact_info_type.email.help';
            !empty($hotelContactInformation->getFullName()) ?: $requiredHotelData[] = 'form.contact_info_type.full_name.help';
            !empty($hotelContactInformation->getPhoneNumber()) ?: $requiredHotelData[] = 'form.contact_info_type.phone.help';
        }
        !empty(count($hotel->getAcceptedCardTypes()) > 0) ?: $requiredHotelData[] = 'form.hotelExtendedType.accepted_card_type.help';
        !empty($hotel->getSmokingPolicy()) ?: $requiredHotelData[] = 'form.hotelType.isSmoking.help';
        !empty($hotel->getCheckinoutPolicy()) ?: $requiredHotelData[] = 'form.hotelExtendedType.check_in_out_policy.label';
        $confirmationUrl == self::TRIP_ADVISOR_CONFIRMATION_URL ?: $requiredHotelData[] = 'channel_manager_helper.confirmation_url';

        return $requiredHotelData;
    }

    /**
     * Проверяет тип комнат на заполнение данных и возвращает названия незаполненных полей
     * @param RoomType $roomType
     * @return array
     */
    public function getRoomTypeRequiredUnfilledFields(RoomType $roomType)
    {
        $requiredRoomTypeData = [];
        !empty($roomType->getInternationalTitle()) ?: $requiredRoomTypeData[] = 'form.roomTypeType.international_title';
        !empty($roomType->getDescription()) ?: $requiredRoomTypeData[] = 'form.roomTypeType.description';
        (in_array('bed', $roomType->getFacilities()) || in_array('double-bed', $roomType->getFacilities()))
            ?: $requiredRoomTypeData[] = 'channel_manager_helper.bed_configuration_not_exists';
        count($roomType->getRoomViewsTypes()) > 0 ?: $requiredRoomTypeData[] = 'form.roomType.room_view_types.label';

        return $requiredRoomTypeData;
    }

    /**
     * Проверяет тариф на заполненность данных, необходимых для синхронизации с TripAdvisor и возвращает данные о незаполненных полях
     * @param Tariff $tariff
     * @return array
     */
    public function getTariffRequiredUnfilledFields(Tariff $tariff)
    {
        $requiredTariffData = [];
        !empty($tariff->getDescription()) ?: $requiredTariffData[] = 'mbhpricebundle.form.tarifftype.opisaniye';

        $mealServicesCodes = array_keys(ServiceData::SERVICES['price.datafixtures.mongodb.servicedata.eat']);
        $firstMealCategoryService = $this->dm
            ->getRepository('MBHPriceBundle:Service')
            ->findOneBy(['code' => current($mealServicesCodes)]);

        $mealTypeCodes = [];
        /** @var TariffService $defaultService */
        foreach ($tariff->getDefaultServices() as $defaultService) {
            if ($defaultService->getService()->getCategory()->getId() === $firstMealCategoryService->getCategory()->getId()) {
                $mealTypeCodes[] = $defaultService->getService()->getCode();
            }
        }
        if (count($mealTypeCodes) == 0) {
            $requiredTariffData[] = 'trip_advisor_helper.meal_data.field_name';
        }

        return $requiredTariffData;
    }

    public function getOrderAvailability(TripAdvisorOrderInfo $orderInfo, $locale)
    {
        $errors = [];
        $isOrderCorrupted = false;

        $isRoomAvailable = true;
        $totalPrice = 0;
        $packages = $orderInfo->getPackagesData();

        $firstPackageInfo = current($packages);
        $searchQuery = new SearchQuery();
        $searchQuery->adults = $firstPackageInfo->getAdultsCount();
        $searchQuery->children = $firstPackageInfo->getChildrenCount();
        $searchQuery->begin = $firstPackageInfo->getBeginDate();
        $searchQuery->end = $firstPackageInfo->getEndDate();
        $searchQuery->tariff = $firstPackageInfo->getTariff();
        $searchQuery->addRoomType($firstPackageInfo->getRoomType()->getId());

        $searchResults = $this->search->search($searchQuery);
        if (count($searchResults) == 0) {
            $isRoomAvailable = false;
        } else {
            $searchResult = current($searchResults);
            foreach ($packages as $packageInfo) {
                if (count($searchResults) == 0) {
                    $isRoomAvailable = false;
                } else {
                    $totalPrice += $searchResult->getPrice($packageInfo->getAdultsCount(),
                        $packageInfo->getChildrenCount());
                }
            }
        }

        if (!$isRoomAvailable || $searchResult->getRoomsCount() < count($packages)) {
            $errors[] = $this->getErrorData(TripAdvisorResponseCompiler::ROOM_NOT_AVAILABLE_ERROR,
                'order_handler.order_room_not_available', $locale);
            $isOrderCorrupted = true;
        }
        //После операций конвертации суммы не совпадают
//        if ($totalPrice != $orderInfo->getPrice()) {
//            $errors[] = $this->getErrorData(TripAdvisorResponseFormatter::PRICE_MISMATCH,
//                'order_handler.price_mismatch.error', $locale);
//            $isOrderCorrupted = true;
//        }
        if (empty($orderInfo->getPayer()->getEmail())) {
            $errors[] = $this->getErrorData(TripAdvisorResponseCompiler::MISSING_EMAIL,
                'order_handler.missing_email.error', $locale);
        }
        if (empty($orderInfo->getPayer()->getFirstName())) {
            $errors[] = $this->getErrorData(TripAdvisorResponseCompiler::MISSING_PAYER_FIRST_NAME,
                'order_handler.missing_first_name.error', $locale);
        }

        $orderPaymentCard = $orderInfo->getCreditCard();
        $creditCardValidationErrors = $this->validator->validate($orderPaymentCard);
        if (is_array($creditCardValidationErrors)) {
            foreach ($creditCardValidationErrors as $cardError) {
                /** @var ConstraintViolation $cardError */
                $errors[] = $this->getErrorData(TripAdvisorResponseCompiler::CREDIT_CARD_DECLINED,
                    $cardError->getMessage(), $locale);
            }
        }

        $acceptedCardTypes = $firstPackageInfo->getRoomType()->getHotel()->getAcceptedCardTypes();
        $acceptedCardCodes = [];
        /** @var CardType $acceptedCardType */
        foreach ($acceptedCardTypes as $acceptedCardType) {
            $acceptedCardCodes[] = $acceptedCardType->getCardCode();
        }
        if (!in_array(strtoupper($orderPaymentCard->type), $acceptedCardCodes)) {
            $errors[] = $this->getErrorData(TripAdvisorResponseCompiler::CREDIT_CARD_NOT_SUPPORTED,
                'order_handler.card_type_not_supported.error', $locale);
        }

        return [
            'isCorrupted' => $isOrderCorrupted,
            'errors' => $errors
        ];
    }

    private function getErrorData($problemType, $descriptionId, $locale)
    {
        return [
            'problem' => $problemType,
            'explanation' => $this->translator->trans($descriptionId, [], null, $locale)
        ];
    }

    /**
     * @param TripAdvisorConfig $config
     */
    public function sendUpdateDataToMBHs(TripAdvisorConfig $config)
    {
        $configData = $this->responseFormatter->formatHotelInventoryData($config);
        $client = new Client();
        $url = 'https://mbhs.maxibooking.ru/client/tripadvisor/update_config';

        $result = $client->post($url, [
            'json' => [
                'configData' => $configData,
                'hotelData' => $config->getHotel(),
                'key' => $this->mbhsKey,
                'isEnabled' => $config->getIsEnabled()
            ]
        ]);
    }
}
<?php

namespace MBH\Bundle\HotelBundle\Form\RoomTypeFlow;

use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Form\ImageType;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BaseBundle\Service\FormDataHandler;
use MBH\Bundle\BaseBundle\Service\HotelSelector;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Model\FlowRuntimeException;
use MBH\Bundle\HotelBundle\Service\FormFlow;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PriceCache;
use MBH\Bundle\PriceBundle\Services\RoomCache;
use Symfony\Component\Form\FormInterface;

class RoomTypeFlow extends FormFlow
{
    const DATE_FORMAT = 'd.m.Y H:i:s';
    const FLOW_TYPE = 'roomType';
    const PREPARATORY_STEPS = [self::HOTEL_STEP, self::ROOM_TYPE_STEP];
    const PREV_STEP_NAME = 'prevStep';

    const HOTEL_STEP = 'hotel';
    const ROOM_TYPE_STEP = 'roomType';
    const ROOM_DESCRIPTION_STEP = 'roomDescription';
    const PHOTOS_STEP = 'photos';
    const NUM_OF_ROOMS_STEP = 'numberOfRooms';
    const ROOM_CACHES_STEP = 'roomCaches';
    const PERIOD_STEP = 'period';
    const TARIFF_STEP = 'tariff';
    const PRICE_STEP = 'price';

    private $hotel;
    private $documentFieldsManager;
    private $formDataHandler;
    private $roomCacheService;
    private $priceCacheService;
    private $hotelSelector;

    private $canChangeStep = true;

    public function __construct(
        DocumentFieldsManager $documentFieldsManager,
        FormDataHandler $formDataHandler,
        RoomCache $roomCacheService,
        PriceCache $priceCacheService,
        HotelSelector $hotelSelector
    )
    {
        $this->documentFieldsManager = $documentFieldsManager;
        $this->formDataHandler = $formDataHandler;
        $this->roomCacheService = $roomCacheService;
        $this->priceCacheService = $priceCacheService;
        $this->hotelSelector = $hotelSelector;
    }

    /**
     * @return string
     */
    public static function getFlowType()
    {
        return self::FLOW_TYPE;
    }

    /**
     * @param string|null $flowId
     * @return $this|FormFlow
     */
    public function init(string $flowId = null)
    {
        parent::init($flowId);

        if (empty($flowId)) {
            if ($this->request->get(self::PREV_STEP_NAME) === self::ROOM_TYPE_STEP) {
                $this->getFlowConfig()->setCurrentStep(2);
            }
        }

        return $this;
    }

    public function getTemplateParameters()
    {
        return ['roomType' => $this->getManagedRoomType()];
    }

    /**
     * @return array
     */
    protected function getStepsConfig(): array
    {
        $flowData = $this->getFlowData();
        $begin = isset($flowData['begin']) ? $this->getDateFromFlowData('begin') : new \DateTime();
        $end = isset($flowData['end']) ? $this->getDateFromFlowData('end') : new \DateTime('+14 days');
        $price = $flowData['price'] ?? null;
        $additionalPrice = $flowData['additionalPrice'] ?? null;
        $roomCaches = $flowData['rooms'] ?? null;

        return [
            [
                'id' => self::HOTEL_STEP,
                'label' => 'room_type_flow.step_labels.hotel',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'hotel' => $this->getManagedHotel()
                ]
            ],
            [
                'id' => self::ROOM_TYPE_STEP,
                'label' => 'room_type_flow.step_labels.room_type',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'hotel' => $this->getManagedHotel(),
                    'roomType' => $this->getManagedRoomType(),
                ],
            ],
            [
                'id' => self::ROOM_DESCRIPTION_STEP,
                'label' => 'room_type_flow.step_labels.room_info',
                'form_type' => RoomTypeFlowType::class,
            ],
            [
                'id' => self::PHOTOS_STEP,
                'label' => 'room_type_flow.step_labels.photos',
                'form_type' => ImageType::class,
                'options' => [
                    'hasConstraints' => false,
                ],
            ],
            [
                'id' => self::NUM_OF_ROOMS_STEP,
                'label' => 'room_type_flow.step_labels.number_of_places',
                'form_type' => RoomTypeFlowType::class,
            ],
            [
                'id' => self::ROOM_CACHES_STEP,
                'label' => 'room_type_flow.step_labels.room_caches',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'rooms' => $roomCaches,
                ],
            ],
            [
                'id' => self::PERIOD_STEP,
                'label' => 'room_type_flow.step_labels.period',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'hotel' => $this->getManagedHotel(),
                    'begin' => $begin,
                    'end' => $end,
                ],
            ],
            [
                'id' => self::TARIFF_STEP,
                'label' => 'room_type_flow.step_labels.tariff',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'hotel' => $this->getManagedHotel(),
                    'tariff' => $this->getFlowDataTariff(),
                ],
            ],
            [
                'id' => self::PRICE_STEP,
                'label' => 'room_type_flow.step_labels.price',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'roomType' => $this->getManagedRoomType(),
                    'price' => $price,
                    'additionalPrice' => $additionalPrice,
                ],
            ],
        ];
    }

    /**
     * @return RoomType|null
     */
    public function getManagedRoomType()
    {
        $flowData = $this->getFlowData();

        return isset($flowData['roomTypeId'])
            ? $this->dm->find(RoomType::class, $flowData['roomTypeId'])
            : null;
    }

    /**
     * @return Hotel|null|object
     */
    public function getManagedHotel()
    {
        if (isset($this->hotel)) {
            return $this->hotel;
        }

        if ($this->request->query->has('hotelId')) {
            return $this->dm->find(Hotel::class, $this->request->query->get('hotelId'));
        }

        if ($this->getManagedRoomType()) {
            return $this->getManagedRoomType()->getHotel();
        }

        return $this->hotelSelector->getSelected();
    }

    protected function getFormData()
    {
        if (in_array($this->getStepId(), [self::ROOM_DESCRIPTION_STEP, self::NUM_OF_ROOMS_STEP])) {
            return $this->getManagedRoomType();
        }

        return null;
    }

    /**
     * @param FormInterface $form
     * @throws \ReflectionException
     * @throws \Exception
     * @throws FlowRuntimeException
     */
    protected function handleForm(FormInterface $form)
    {
        $formData = $form->getData();
        if (in_array($this->getStepId(), [
            self::HOTEL_STEP,
            self::ROOM_TYPE_STEP,
            self::ROOM_CACHES_STEP,
            self::PERIOD_STEP,
            self::TARIFF_STEP,
            self::PRICE_STEP
        ])) {
            $flowConfig = $this->getFlowConfig();
            $flowData = $flowConfig->getFlowData();

            switch ($this->getStepId()) {
                case self::HOTEL_STEP:
                    $this->hotel = $formData['hotel'];
                    break;
                case self::ROOM_TYPE_STEP:
                    /** @var RoomType $roomType */
                    $roomType = $formData['roomType'];
                    $flowData['roomTypeId'] = $roomType->getId();
                    $existingConfig = $this->findFlowConfig($roomType->getId());
                    if (!is_null($existingConfig)) {
                        if ($existingConfig->getCurrentStepNumber() !== $this->getCurrentStepNumber()
                            && !in_array($this->getStepId(), self::PREPARATORY_STEPS)) {
                            $this->canChangeStep = false;
                        } else {
                            $existingConfig->setCurrentStep(2);
                        }
                        $this->flowConfig = $existingConfig;
                    } else {
                        $flowConfig->setFlowId($roomType->getId());
                    }
                    break;
                case self::PERIOD_STEP:
                    /** @var \DateTime $begin */
                    $begin = $formData['begin'];
                    /** @var \DateTime $end */
                    $end = $formData['end'];

                    $flowData['begin'] = $begin->format(self::DATE_FORMAT);
                    $flowData['end'] = $end->format(self::DATE_FORMAT);
                    break;
                case self::TARIFF_STEP:
                    /** @var Tariff $tariff */
                    $tariff = $formData['tariff'];
                    $flowData['pricesTariffId'] = $tariff->getId();
                    break;
                default:
                    $flowData = array_replace($flowData, $formData);
                    break;
            }

            $flowConfig->setFlowData($flowData);
        }

        if (in_array($this->getStepId(), [self::ROOM_DESCRIPTION_STEP])) {
            $multiLangFields = $this->documentFieldsManager
                ->getPropertiesByAnnotationClass(RoomType::class);
            $this->formDataHandler
                ->saveTranslationsFromMultipleFieldsForm($form, $this->request, $multiLangFields);
        }

        if ($this->getStepId() === self::PHOTOS_STEP && $form->getData() instanceOf Image && $form->getData()->getImageFile()) {
            $onlineImage = $form->getData();
            $roomType = $this->getManagedRoomType();
            $roomType->addOnlineImage($onlineImage);
            if ($onlineImage->getIsDefault()) {
                $roomType->makeMainImage($onlineImage);
            };
        }

        if ($this->isFinishButtonClicked()) {
            $error = $this->roomCacheService
                ->update(
                    $this->getDateFromFlowData('begin'),
                    $this->getDateFromFlowData('end'),
                    $this->getManagedHotel(),
                    $this->getFlowData()['rooms'],
                    false,
                    [$this->getManagedRoomType()]
                );

            if ($error !== '') {
                $this->getFlowConfig()->setCurrentStep(5);
                throw new FlowRuntimeException($error);
            }

            $this->priceCacheService
                ->update(
                    $this->getDateFromFlowData('begin'),
                    $this->getDateFromFlowData('end'),
                    $this->getManagedHotel(),
                    $this->getFlowData()['price'],
                    false,
                    null,
                    $this->getFlowData()['additionalPrice'] ?? null,
                    null,
                    [$this->getManagedRoomType()],
                    [$this->getFlowDataTariff()]
                );
        }

        $this->dm->flush();
    }

    protected function mustChangeStep(): bool
    {
        return $this->canChangeStep && parent::mustChangeStep();
    }

    /**
     * @return bool|int|null
     */
    public function isPreparatoryStep()
    {
        return in_array($this->getStepId(), self::PREPARATORY_STEPS);
    }

    /**
     * @param $fieldName
     * @return bool|\DateTime
     */
    private function getDateFromFlowData($fieldName)
    {
        return \DateTime::createFromFormat(self::DATE_FORMAT, $this->getFlowData()[$fieldName]);
    }

    /**
     * @return \MBH\Bundle\PriceBundle\Document\Tariff|null|object
     */
    private function getFlowDataTariff()
    {
        return isset($this->getFlowData()['pricesTariffId'])
            ? $this->dm->find('MBHPriceBundle:Tariff', $this->getFlowData()['pricesTariffId'])
            : null;
    }

    public function addSuccessFinishFlash()
    {
        $this->addFlash($this->translator->trans('room_type_flow.success_finish_message', [
            '%roomTypeName%' => $this->getManagedRoomType()->getName()
        ]));
    }
}
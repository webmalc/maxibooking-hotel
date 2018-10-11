<?php

namespace MBH\Bundle\HotelBundle\Form\RoomTypeFlow;

use MBH\Bundle\BaseBundle\Form\ImageType;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BaseBundle\Service\FormDataHandler;
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

    private $hotel;
    private $documentFieldsManager;
    private $formDataHandler;
    private $roomCacheService;
    private $priceCacheService;

    public function __construct(
        DocumentFieldsManager $documentFieldsManager,
        FormDataHandler $formDataHandler,
        RoomCache $roomCacheService,
        PriceCache $priceCacheService
    )
    {
        $this->documentFieldsManager = $documentFieldsManager;
        $this->formDataHandler = $formDataHandler;
        $this->roomCacheService = $roomCacheService;
        $this->priceCacheService = $priceCacheService;
    }

    /**
     * @return string
     */
    public static function getFlowType()
    {
        return self::FLOW_TYPE;
    }

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
                'label' => 'room_type_flow.step_labels.hotel',
                'form_type' => RoomTypeFlowType::class
            ],
            [
                'label' => 'room_type_flow.step_labels.room_type',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'hotel' => $this->hotel,
                    'roomType' => $this->getManagedRoomType(),
                ],
            ],
            [
                'label' => 'room_type_flow.step_labels.room_info',
                'form_type' => RoomTypeFlowType::class,
            ],
            [
                'label' => 'room_type_flow.step_labels.photos',
                'form_type' => ImageType::class,
                'options' => [
                    'hasConstraints' => false,
                ],
            ],
            [
                'label' => 'room_type_flow.step_labels.number_of_places',
                'form_type' => RoomTypeFlowType::class,
            ],
            [
                'label' => 'room_type_flow.step_labels.room_caches',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'rooms' => $roomCaches,
                ],
            ],
            [
                'label' => 'room_type_flow.step_labels.period',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'hotel' => $this->hotel,
                    'begin' => $begin,
                    'end' => $end,
                ],
            ],
            [
                'label' => 'room_type_flow.step_labels.tariff',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'hotel' => $this->hotel,
                    'tariff' => $this->getFlowDataTariff(),
                ],
            ],
            [
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

    protected function getFormData()
    {
        if (in_array($this->getCurrentStepNumber(), [2, 4])) {
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
        if (in_array($this->getCurrentStepNumber(), [1, 5, 6, 7, 8, 9])) {
            $flowConfig = $this->getFlowConfig();
            $flowData = $flowConfig->getFlowData();
            switch ($this->getCurrentStepNumber()) {
                case 1:
                    break;
                case 2:
                    /** @var RoomType $roomType */
                    $roomType = $formData['roomType'];
                    $flowData['roomTypeId'] = $roomType->getId();
                    break;
                case 7:
                    /** @var \DateTime $begin */
                    $begin = $formData['begin'];
                    /** @var \DateTime $end */
                    $end = $formData['end'];

                    $flowData['begin'] = $begin->format(self::DATE_FORMAT);
                    $flowData['end'] = $end->format(self::DATE_FORMAT);
                    break;
                case 8:
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

        if (in_array($this->getCurrentStepNumber(), [3])) {
            $multiLangFields = $this->documentFieldsManager
                ->getPropertiesByAnnotationClass(RoomType::class);
            $this->formDataHandler
                ->saveTranslationsFromMultipleFieldsForm($form, $this->request, $multiLangFields);
        }

        if ($this->getCurrentStepNumber() === 4 && $form->getData() && $form->getData()->getImageFile()) {
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
                    $this->hotel,
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
                    $this->hotel,
                    $this->getFlowData()['price'],
                    $this->getFlowData()['isPersonPrice'],
                    null,
                    $this->getFlowData()['additionalPrice'] ?? null,
                    null,
                    [$this->getManagedRoomType()],
                    [$this->getFlowDataTariff()]
                );
        }

        $this->dm->flush();
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
}
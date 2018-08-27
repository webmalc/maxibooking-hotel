<?php

namespace MBH\Bundle\HotelBundle\Form\RoomTypeFlow;

use Gedmo\Translatable\Translatable;
use MBH\Bundle\BaseBundle\Form\ImageType;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BaseBundle\Service\FormDataHandler;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Model\FlowRuntimeException;
use MBH\Bundle\HotelBundle\Service\FormFlow;
use MBH\Bundle\PriceBundle\Services\PriceCache;
use MBH\Bundle\PriceBundle\Services\RoomCache;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class RoomTypeFlow extends FormFlow
{
    const DATE_FORMAT = 'd.m.Y';

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
    ) {
        $this->documentFieldsManager = $documentFieldsManager;
        $this->formDataHandler = $formDataHandler;
        $this->roomCacheService = $roomCacheService;
        $this->priceCacheService = $priceCacheService;
    }

    public function setInitData(Hotel $hotel)
    {
        $this->hotel = $hotel;

        return $this;
    }

    protected function getStepsConfig(): array
    {
        $flowData = $this->getFlowData();
        $begin = isset($flowData['begin']) ? $this->getDateFromFlowData('begin') : new \DateTime();
        $end = isset($flowData['end']) ? $this->getDateFromFlowData('end') : new \DateTime('+14 days');
        $isPersonPrice = $flowData['isPersonPrice'] ?? false;
        $price = $flowData['price'] ?? null;
        $additionalPrice = $flowData['additionalPrice'] ?? null;
        $roomCaches = $flowData['rooms'] ?? null;

        return [
            [
                'label' => 'Тип номера',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'hotel' => $this->hotel,
                    'roomType' => $this->getManagedRoomType(),
                ],
            ],
            [
                'label' => 'Информация о номере',
                'form_type' => RoomTypeFlowType::class,
            ],
            [
                'label' => 'Фотографии',
                'form_type' => ImageType::class,
                'options' => [
                    'hasConstraints' => false,
                ],
            ],
            [
                'label' => 'Количество мест',
                'form_type' => RoomTypeFlowType::class,
            ],
            [
                'label' => 'Номера в продаже',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'rooms' => $roomCaches,
                ],
            ],
            [
                'label' => 'Тип цен',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'hotel' => $this->hotel,
                    'isPersonPrice' => $isPersonPrice,
                ],
            ],
            [
                'label' => 'Период',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'hotel' => $this->hotel,
                    'begin' => $begin,
                    'end' => $end,
                ],
            ],
            [
                'label' => 'Тариф',
                'form_type' => RoomTypeFlowType::class,
                'options' => [
                    'hotel' => $this->hotel,
                    'tariff' => $this->getFlowDataTariff(),
                ],
            ],
            [
                'label' => 'Цена',
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
            if ($this->getCurrentStepNumber() === 1) {
                $flowData['roomTypeId'] = $formData['roomType']->getId();
            } elseif ($this->getCurrentStepNumber() === 8) {
                $flowData['pricesTariffId'] = $formData['tariff']->getId();
            } elseif ($this->getCurrentStepNumber() === 7) {
                $flowData['begin'] = $formData['begin']->format(self::DATE_FORMAT);
                $flowData['end'] = $formData['end']->format(self::DATE_FORMAT);
            } else {
                $flowData = array_replace($flowData, $formData);
            }

            $flowConfig->setFlowData($flowData);
        }

        if (in_array($this->getCurrentStepNumber(), [2])) {
            $multiLangFields = $this->documentFieldsManager
                ->getPropertiesByAnnotationClass(RoomType::class);
            $this->formDataHandler
                ->saveTranslationsFromMultipleFieldsForm($form, $this->request, $multiLangFields);
        }

        if ($this->getCurrentStepNumber() === 3 && $form->getData() && $form->getData()->getImageFile()) {
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
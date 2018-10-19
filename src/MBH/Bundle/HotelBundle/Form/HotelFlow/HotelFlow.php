<?php

namespace MBH\Bundle\HotelBundle\Form\HotelFlow;

use Gedmo\Mapping\Annotation\Translatable;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BaseBundle\Service\FormDataHandler;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\HotelImageType;
use MBH\Bundle\HotelBundle\Service\FormFlow;
use Symfony\Component\Form\FormInterface;

class HotelFlow extends FormFlow
{
    const FLOW_TYPE = 'hotel';

    const HOTEL_STEP = 'hotel';
    const DESC_STEP = 'hotelDescription';
    const LOGO_STEP = 'logo';
    const ADDRESS_STEP = 'address';
    const COORDINATES_STEP = 'coordinates';
    const CONTACTS_STEP = 'contacts';
    const MAIN_PHOTO_STEP = 'mainPhoto';
    const PHOTOS_STEP = 'photos';

    private $documentFieldsManager;
    private $formDataHandler;

    private $canChangeStep = true;

    public function __construct(DocumentFieldsManager $documentFieldsManager, FormDataHandler $formDataHandler)
    {
        $this->documentFieldsManager = $documentFieldsManager;
        $this->formDataHandler = $formDataHandler;
    }

    public static function getFlowType()
    {
        return self::FLOW_TYPE;
    }

    public function getTemplateParameters()
    {
        return ['hotel' => $this->getManagedHotel()];
    }

    /**
     * @return array
     */
    protected function getStepsConfig(): array
    {
        return [
            [
                'id' => self::HOTEL_STEP,
                'label' => 'hotel_flow.step_labels.hotel_name',
                'form_type' => HotelFlowType::class,
            ],
            [
                'id' => self::DESC_STEP,
                'label' => 'hotel_flow.step_labels.hotel_description',
                'form_type' => HotelFlowType::class,
            ],
            [
                'id' => self::LOGO_STEP,
                'label' => 'hotel_flow.step_labels.hotel_logo',
                'form_type' => HotelFlowType::class,
            ],
            [
                'id' => self::ADDRESS_STEP,
                'label' => 'hotel_flow.step_labels.hotel_address',
                'form_type' => HotelAddressType::class,
            ],
            [
                'id' => self::COORDINATES_STEP,
                'label' => 'hotel_flow.step_labels.hotel_coordinates',
                'form_type' => HotelLocationType::class,
            ],
            [
                'id' => self::CONTACTS_STEP,
                'label' => 'hotel_flow.step_labels.contacts',
                'form_type' => HotelFlowType::class,
            ],
            [
                'id' => self::MAIN_PHOTO_STEP,
                'label' => 'hotel_flow.step_labels.main_photo',
                'form_type' => HotelFlowType::class,
            ],
            [
                'id' => self::PHOTOS_STEP,
                'label' => 'hotel_flow.step_labels.photos',
                'form_type' => HotelImageType::class,
                'options' => [
                    'withIsDefaultField' => false,
                    'hasConstraints' => false,
                ],
            ],
        ];
    }

    /**
     * @return Hotel|null
     */
    protected function getFormData()
    {
        return in_array($this->getStepId(), [self::PHOTOS_STEP, self::HOTEL_STEP])
            ? null
            : $this->getManagedHotel();
    }

    /**
     * @return Hotel|null|object
     */
    private function getManagedHotel()
    {
        if ($this->getFlowConfig()->getFlowId()) {
            return $this->dm->find(Hotel::class, $this->getFlowConfig()->getFlowId());
        }

        return null;
    }
    /**
     * @return bool
     */
    protected function mustChangeStep(): bool
    {
        return $this->canChangeStep && parent::mustChangeStep();
    }

    /**
     * @param FormInterface $form
     * @throws \ReflectionException
     */
    protected function handleForm(FormInterface $form)
    {
        if ($this->getStepId() === self::HOTEL_STEP) {
            /** @var Hotel $hotel */
            $hotel = $form->getData()['hotel'];
            $existingConfig = $this->findFlowConfig($hotel->getId());
            if (!is_null($existingConfig)) {
                if ($existingConfig->getCurrentStepNumber() !== $this->getCurrentStepNumber()) {
                    $this->canChangeStep = false;
                }
                $existingConfig->setIsFinished(false);
                $this->flowConfig = $existingConfig;
            } else {
                $this->getFlowConfig()->setFlowId($hotel->getId());
            }
        }

        if (in_array($this->getStepId(), [self::DESC_STEP, self::ADDRESS_STEP])) {
            $multiLangFields = $this->documentFieldsManager
                ->getPropertiesByAnnotationClass(Hotel::class, Translatable::class);
            $this->formDataHandler
                ->saveTranslationsFromMultipleFieldsForm($form, $this->request, $multiLangFields);
        }

        if ($this->getStepId() === self::MAIN_PHOTO_STEP && $this->getManagedHotel()->getDefaultImage()) {
            $this->dm->persist($this->getManagedHotel()->getDefaultImage());
        }

        if ($this->getStepId() === self::PHOTOS_STEP && $form->getData() instanceOf Image && $form->getData()->getImageFile()) {
            $savedImage = $form->getData();
            $this->getManagedHotel()->addImage($savedImage);
            $this->dm->persist($savedImage);
        }

        $this->dm->flush();
    }
}
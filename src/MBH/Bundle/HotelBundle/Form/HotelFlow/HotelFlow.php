<?php

namespace MBH\Bundle\HotelBundle\Form\HotelFlow;

use Gedmo\Mapping\Annotation\Translatable;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BaseBundle\Service\FormDataHandler;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\HotelImageType;
use MBH\Bundle\HotelBundle\Service\FormFlow;
use Symfony\Component\Form\FormInterface;

class HotelFlow extends FormFlow
{
    const FLOW_TYPE = 'hotel';

    const NAME_STEP = 'hotelName';
    const DESC_STEP = 'hotelDescription';
    const LOGO_STEP = 'logo';
    const ADDRESS_STEP = 'address';
    const COORDINATES_STEP = 'coordinates';
    const CONTACTS_STEP = 'contacts';
    const MAIN_PHOTO_STEP = 'mainPhoto';
    const PHOTOS_STEP = 'photos';

    /** @var Hotel */
    private $hotel;
    private $documentFieldsManager;
    private $formDataHandler;

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
        return ['hotel' => $this->hotel];
    }

    /**
     * @return array
     */
    protected function getStepsConfig(): array
    {
        return [
            [
                'id' => self::NAME_STEP,
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
        return in_array($this->getStepId(), [self::MAIN_PHOTO_STEP]) ? null : $this->hotel;
    }

    /**
     * @param FormInterface $form
     * @throws \ReflectionException
     */
    protected function handleForm(FormInterface $form)
    {
        if (in_array($this->getStepId(), [self::NAME_STEP, self::DESC_STEP, self::ADDRESS_STEP])) {
            $multiLangFields = $this->documentFieldsManager
                ->getPropertiesByAnnotationClass(Hotel::class, Translatable::class);
            $this->formDataHandler
                ->saveTranslationsFromMultipleFieldsForm($form, $this->request, $multiLangFields);
        }

        if ($this->getStepId() === self::MAIN_PHOTO_STEP) {
            $this->dm->persist($this->hotel->getDefaultImage());
        }

        if ($this->getStepId() === self::PHOTOS_STEP && !$this->isBackButtonClicked()) {
            $savedImage = $form->getData();
            $this->hotel->addImage($savedImage);
            $this->dm->persist($savedImage);
        }

        $this->dm->flush();
    }
}
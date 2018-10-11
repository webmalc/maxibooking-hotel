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

    /**
     * @return array
     */
    protected function getStepsConfig(): array
    {
        return [
            [
                'label' => 'hotel_flow.step_labels.hotel_name',
                'form_type' => HotelFlowType::class,
            ],
            [
                'label' => 'hotel_flow.step_labels.hotel_description',
                'form_type' => HotelFlowType::class,
            ],
            [
                'label' => 'hotel_flow.step_labels.hotel_logo',
                'form_type' => HotelFlowType::class,
            ],
            [
                'label' => 'hotel_flow.step_labels.hotel_address',
                'form_type' => HotelAddressType::class,
            ],
            [
                'label' => 'hotel_flow.step_labels.hotel_coordinates',
                'form_type' => HotelLocationType::class,
            ],
            [
                'label' => 'hotel_flow.step_labels.contacts',
                'form_type' => HotelFlowType::class,
            ],
            [
                'label' => 'hotel_flow.step_labels.main_photo',
                'form_type' => HotelFlowType::class,
            ],
            [
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
        return in_array($this->getCurrentStepNumber(), [8]) ? null : $this->hotel;
    }

    /**
     * @param FormInterface $form
     * @throws \ReflectionException
     */
    protected function handleForm(FormInterface $form)
    {
        if (in_array($this->getCurrentStepNumber(), [1, 2, 4])) {
            $multiLangFields = $this->documentFieldsManager
                ->getPropertiesByAnnotationClass(Hotel::class, Translatable::class);
            $this->formDataHandler
                ->saveTranslationsFromMultipleFieldsForm($form, $this->request, $multiLangFields);
        }

        if ($this->getCurrentStepNumber() === 7) {
            $this->dm->persist($this->hotel->getDefaultImage());
        }

        if ($this->getCurrentStepNumber() === 8 && !$this->isBackButtonClicked()) {
            $savedImage = $form->getData();
            $this->hotel->addImage($savedImage);
            $this->dm->persist($savedImage);
        }

        $this->dm->flush();
    }
}
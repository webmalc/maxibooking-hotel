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
    /** @var Hotel */
    private $hotel;
    private $documentFieldsManager;
    private $formDataHandler;

    public function __construct(DocumentFieldsManager $documentFieldsManager, FormDataHandler $formDataHandler)
    {
        $this->documentFieldsManager = $documentFieldsManager;
        $this->formDataHandler = $formDataHandler;
    }

    /**
     * @param Hotel $hotel
     * @return HotelFlow
     */
    public function setInitData(Hotel $hotel)
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * @return array
     */
    protected function getStepsConfig(): array
    {
        return [
            [
                'label' => 'Ввод имени отеля',
                'form_type' => HotelFlowType::class,
            ],
            [
                'label' => 'Ввод описания отеля',
                'form_type' => HotelFlowType::class,
            ],
            [
                'label' => 'Логотип отеля',
                'form_type' => HotelFlowType::class,
            ],
            [
                'label' => 'Адрес отеля',
                'form_type' => HotelAddressType::class,
            ],
            [
                'label' => 'Координаты отеля на карте',
                'form_type' => HotelLocationType::class,
            ],
            [
                'label' => 'Контакты',
                'form_type' => HotelFlowType::class,
            ],
            [
                'label' => 'Главная фотография',
                'form_type' => HotelFlowType::class,
            ],
            [
                'label' => 'Фотографии',
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
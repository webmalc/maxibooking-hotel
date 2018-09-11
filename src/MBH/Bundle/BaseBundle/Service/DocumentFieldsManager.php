<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\Common\Collections\Collection;
use MBH\Bundle\HotelBundle\Document\ContactInfo;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Translation\TranslatorInterface;

class DocumentFieldsManager
{
    const NAMES_TRANS_IDS = [
        Hotel::class => [
            'description' => 'site_manager.description.hotel',
            'logoImage' => 'form.hotel_logo.image_file.help',
            'contactInformation' => 'form.hotel_contact_information.contact_info.group',
            'latitude' => 'form.hotelExtendedType.latitude',
            'longitude' => 'form.hotelExtendedType.longitude',
            'images' => 'site_manager.photos_tab.hotel',
            'street' => 'form.hotelExtendedType.street',
            'zipCode' => 'form.hotelExtendedType.zip_code',
            'settlement' => 'form.hotelExtendedType.settlement',
            'cityId' => 'form.hotelExtendedType.city',
            'house' => 'form.hotelExtendedType.house'
        ],
        RoomType::class => [
            'description' => 'form.roomTypeType.description',
            'roomSpace' => 'form.roomTypeType.room_space',
            'facilities' => 'form.facilitiesType.label',
            'onlineImages' => 'site_manager.photos_tab.room'
        ]
    ];

    const INCONSISTENT_DOC_FIELD_TO_FORM_FIELD = [
        Hotel::class => [
            'images' => 'imageFile'
        ],
        RoomType::class => [
            'onlineImages' => 'imageFile'
        ]
    ];

    const CORRECT_FIELD_STATUS = 'correct';
    const EMPTY_FIELD_STATUS = 'empty';

    private $translator;
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(TranslatorInterface $translator, PropertyAccessor $accessor)
    {
        $this->translator = $translator;
        $this->accessor = $accessor;
    }

    /**
     * @param string $documentName
     * @param string $fieldName
     * @param bool $isTranslated
     * @return string
     */
    public function getFieldName(string $documentName, string $fieldName, $isTranslated = true)
    {
        if (isset(self::NAMES_TRANS_IDS[$documentName][$fieldName])) {
            $transId = self::NAMES_TRANS_IDS[$documentName][$fieldName];

            return $isTranslated ? $this->translator->trans($transId) : $transId;
        }

        throw new \InvalidArgumentException('Field "' . $fieldName . '" of the document ' . $documentName . ' is not found!');
    }

    /**
     * @param array $fieldsDataByNames
     * @param $document
     * @return array
     */
    public function getFieldsByCorrectnessStatuses(array $fieldsDataByNames, $document)
    {
        $checkedFields = [self::EMPTY_FIELD_STATUS => [], self::CORRECT_FIELD_STATUS => []];
        foreach ($fieldsDataByNames as $field) {
            $fieldData = $this->accessor->getValue($document, $field);
            $isFieldEmpty = $this->isFieldEmpty($fieldData);

            $correctnessType = $isFieldEmpty ? self::EMPTY_FIELD_STATUS : self::CORRECT_FIELD_STATUS;
            $checkedFields[$correctnessType][] = $field;
        }

        return $checkedFields;
    }

    /**
     * @param $fieldData
     * @return bool
     */
    private function isFieldEmpty($fieldData): bool
    {
        if ($fieldData instanceof Collection) {
            return $fieldData->count() === 0;
        }

        if ($fieldData instanceof ContactInfo) {
            return !($this->accessor->getValue($fieldData, 'email') && $this->accessor->getValue($fieldData, 'phoneNumber'));
        }

        return empty($fieldData);
    }

    /**
     * @param string $documentName
     * @param string $fieldName
     * @return string
     */
    public function getFormFieldByDocumentField(string $documentName, string $fieldName)
    {
        return isset(self::INCONSISTENT_DOC_FIELD_TO_FORM_FIELD[$documentName][$fieldName])
            ? self::INCONSISTENT_DOC_FIELD_TO_FORM_FIELD[$documentName][$fieldName]
            : $fieldName;
    }
}
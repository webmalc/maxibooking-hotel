<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\PhpParser;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Date;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Integer;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne;
use MBH\Bundle\ApiBundle\Lib\HotelIdsParamTrait;
use MBH\Bundle\ApiBundle\Lib\LimitedTrait;
use MBH\Bundle\ApiBundle\Lib\RequestParams;
use MBH\Bundle\ApiBundle\Lib\RoomTypesRequestParams;
use MBH\Bundle\ApiBundle\Lib\TariffRequestParams;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Lib\Normalization\BooleanFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\CollectionFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\CustomFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\DateTimeFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\DocumentFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\DocumentsCollectionFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\EmbedManyFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\EmbedOneFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\FloatFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\IntegerFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\StringFieldType;
use Gedmo\Mapping\Annotation\Translatable;
use MBH\Bundle\HotelBundle\Document\ContactInfo;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Translation\TranslatorInterface;

class DocumentFieldsManager
{
    const NAMES_TRANS_IDS = [
        Hotel::class => [
            'description' => 'site_manager.description.hotel',
            'logoImage' => 'form.hotel_logo.image_file.help',
            'contactInformation' => 'site_manager.contactInformation.label',
            'latitude' => 'form.hotelExtendedType.latitude',
            'longitude' => 'form.hotelExtendedType.longitude',
            'images' => 'site_manager.photos_tab.hotel',
            'street' => 'form.hotelExtendedType.street',
            'zipCode' => 'form.hotelExtendedType.zip_code',
            'settlement' => 'form.hotelExtendedType.settlement',
            'cityId' => 'form.hotelExtendedType.city',
            'house' => 'form.hotelExtendedType.house',
            'mapUrl' => 'form.hotel_contact_information_type.map_url'
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

    const DOCTRINE_ANNOTATION_TYPES_TO_FIELD_TYPES = [
        ReferenceOne::class => ['class' => DocumentFieldType::class, 'isDocument' => true],
        ReferenceMany::class => ['class' => DocumentsCollectionFieldType::class, 'isDocument' => true],
        EmbedOne::class => ['class' => EmbedOneFieldType::class, 'isDocument' => true],
        EmbedMany::class => ['class' => EmbedManyFieldType::class, 'isDocument' => true],
        Date::class => ['class' => DateTimeFieldType::class, 'isDocument' => false],
        Id::class => ['class' => StringFieldType::class, 'isDocument' => false],
        Integer::class => ['class' => IntegerFieldType::class, 'isDocument' => false]
    ];

    const CORRECT_FIELD_STATUS = 'correct';
    const EMPTY_FIELD_STATUS = 'empty';

    private $classUseStatements;
    private $isClassUseStatementsInit = false;
    private $normalizationFieldTypes = [];

    private $translator;
    /** @var PropertyAccessor */
    private $accessor;
    private $annotationReader;
    private $helper;

    public function __construct(
        TranslatorInterface $translator,
        PropertyAccessor $accessor,
        CachedReader $reader,
        Helper $helper
    )
    {
        $this->translator = $translator;
        $this->accessor = $accessor;
        $this->annotationReader = $reader;
        $this->helper = $helper;

        $this->normalizationFieldTypes = $this->getSpecialNormalizationFieldsTypes();
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

    /**
     * @param string $className
     * @param string $annotationName
     * @return array
     * @throws \ReflectionException
     */
    public function getPropertiesByAnnotationClass(string $className, string $annotationName = Translatable::class)
    {
        $result = [];
        $classProperties = (new \ReflectionClass($className))->getProperties();
        foreach ($classProperties as $property) {
            if ($this->annotationReader->getPropertyAnnotation($property, $annotationName)) {
                $result[] = $property->getName();
            }
        }

        return $result;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return array
     */
    public function getClassUseStatements(\ReflectionClass $reflectionClass)
    {
        if (!$this->isClassUseStatementsInit) {
            $this->classUseStatements = (new PhpParser())->parseClass($reflectionClass);
            $this->isClassUseStatementsInit = true;
        }

        return $this->classUseStatements;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param $shortName
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getClassFullNameByShortNameFromUseStatements(\ReflectionClass $reflectionClass, $shortName)
    {
        $useStatements = $this->getClassUseStatements($reflectionClass);
        $lowerCaseShortName = mb_strtolower($shortName);

        if (isset($useStatements[$lowerCaseShortName])) {
            return $useStatements[$lowerCaseShortName];
        }


        if (class_exists($shortName)) {
            return $shortName;
        }

        $classInCurrentNamespace = $reflectionClass->getNamespaceName() . '\\' . $shortName;
        if (class_exists($classInCurrentNamespace)) {
            return $classInCurrentNamespace;
        }

        throw new \InvalidArgumentException('Class ' . $shortName . ' doesn\'t used in class ' . $reflectionClass->getName());
    }

    /**
     * @param \ReflectionProperty $property
     * @return BooleanFieldType|DateTimeFieldType|IntegerFieldType|StringFieldType|FloatFieldType|CollectionFieldType
     * @throws \InvalidArgumentException
     */
    public function getFieldType(\ReflectionProperty $property)
    {
        $class = $property->class;
        if (!isset($this->normalizationFieldTypes[$class][$property->getName()])) {
            if (!isset($this->normalizationFieldTypes[$class])) {
                $this->normalizationFieldTypes[$class] = [];
            }

            $this->normalizationFieldTypes[$class][$property->getName()] = $this->calcFieldType($property);
        }

        return $this->normalizationFieldTypes[$class][$property->getName()];
    }

    /**
     * Return class fields types data. Used for classes that don't have doctrine annotations or have some special settings
     *
     * @return array
     */
    private function getSpecialNormalizationFieldsTypes(): array
    {
        $traitFields = [
            LimitedTrait::class => [
                'limit' => new IntegerFieldType(),
                'skip' => new IntegerFieldType()
            ],
            HotelIdsParamTrait::class => [
                'hotelIds' => new CollectionFieldType()
            ]
        ];

        $requestParamsTypes = [
            'isEnabled' => new BooleanFieldType(),
            'ids' => new CollectionFieldType()
        ];

        $fieldTypes = [
            SearchResult::class => [
                'begin' => new DateTimeFieldType(),
                'end' => new DateTimeFieldType(),
                'adults' => new IntegerFieldType(),
                'children' => new IntegerFieldType(),
                'packagesCount' => new IntegerFieldType(),
                'roomType' => new DocumentFieldType(RoomType::class),
                'virtualRoom' => new DocumentFieldType(Room::class),
                'tariff' => new DocumentFieldType(Tariff::class),
                'prices' => new CollectionFieldType(new FloatFieldType()),
                'pricesByDate' => new CollectionFieldType(new CollectionFieldType(new FloatFieldType())),
                'roomsCount' => new IntegerFieldType(),
                'rooms' => new DocumentsCollectionFieldType(Room::class),
                'packagePrices' => new CollectionFieldType(new CollectionFieldType(new EmbedOneFieldType(PackagePrice::class))),
                'useCategories' => new BooleanFieldType(),
                'forceBooking' => new BooleanFieldType(),
                'infants' => new IntegerFieldType(),
                'queryId' => new StringFieldType()
            ],
            PackageQueryCriteria::class => [
                'begin' => new DateTimeFieldType(),
                'end' => new DateTimeFieldType(),
                'query' => new StringFieldType(),
                'liveBegin' => new DateTimeFieldType(),
                'liveEnd' => new DateTimeFieldType(),
                'createdBy' => new StringFieldType(),
                'filter' => new StringFieldType(),
                'checkIn' => new DateTimeFieldType(),
                'checkOut' => new DateTimeFieldType(),
                'hotel' => new DocumentFieldType(Hotel::class),
                'order' => new DocumentFieldType(Order::class),
                'packageOrder' => new DocumentFieldType(Order::class),
                'packageOrders' => new CollectionFieldType(),
                'roomTypes' => new CollectionFieldType(),
                'isConfirmed' => new BooleanFieldType(),
                'paidStatus' => new StringFieldType(),
                'status' => new StringFieldType(),
                'sort' => new CollectionFieldType(),
                'skip' => new IntegerFieldType(),
                'limit' => new IntegerFieldType(),
                'dateFilterBy' => new StringFieldType(),
                'deleted' => new BooleanFieldType(),
                'hasAccommodations' => new BooleanFieldType(),
                'accommodations' => new CollectionFieldType(),
                'sources' => new CollectionFieldType()
            ],
            RequestParams::class => $requestParamsTypes,
            RoomTypesRequestParams::class => array_merge(
                $requestParamsTypes,
                $traitFields[LimitedTrait::class],
                $traitFields[HotelIdsParamTrait::class]
            ),
            TariffRequestParams::class => array_merge($requestParamsTypes, [
                'isOnline' => new BooleanFieldType()
            ]),
            RoomType::class => [
                'onlineImages' => new CollectionFieldType(new CustomFieldType([$this, 'getImageData']))
            ],
            Hotel::class => [
                'images' => new CollectionFieldType(new CustomFieldType([$this, 'getImageData'])),
                'mapImage' => new CustomFieldType([$this, 'getImageData']),
                'file' => new CustomFieldType([$this, 'getImageData'])
            ]
        ];

        return $fieldTypes;
    }

    /**
     * @param \ReflectionProperty $property
     * @return mixed
     */
    private function calcFieldType(\ReflectionProperty $property)
    {
        $annotation = $this->annotationReader->getPropertyAnnotation($property, Field::class);

        if (!is_null($annotation)) {
            switch ($annotation->type) {
                case 'boolean':
                case 'bool':
                    return new BooleanFieldType();
                case 'string':
                    return new StringFieldType();
                case 'float':
                case 'numeric':
                    return new FloatFieldType();
                case 'collection':
                    return new CollectionFieldType();
                case 'int':
                case 'integer':
                    return new IntegerFieldType();
                case 'date':
                    return new DateTimeFieldType();
            }
        }

        foreach (self::DOCTRINE_ANNOTATION_TYPES_TO_FIELD_TYPES as $doctrineType => $fieldTypeData) {
            $annotation = $this->annotationReader->getPropertyAnnotation($property, $doctrineType);
            if (!is_null($annotation)) {
                $fieldTypeClass = $fieldTypeData['class'];

                if ($fieldTypeData['isDocument']) {
                    $className
                        = $this->getClassFullNameByShortNameFromUseStatements($property->getDeclaringClass(), $annotation->targetDocument);

                    return new $fieldTypeClass($className);
                }

                return new $fieldTypeClass();
            }
        }

        $exceptionMessage = 'Can not detect type of field by doctrine annotation for field "'
            . $property->getName()
            . '" of class "'
            . $property->class
            . '". If it is deprecated annotation type, consider to replace it with modern field annotation.';

        throw new \InvalidArgumentException($exceptionMessage);
    }

    /**
     * @param Image $image
     * @return array
     */
    public function getImageData(Image $image)
    {
        $roomTypeImageData = ['isMain' => $image->getIsDefault()];
        $roomTypeImageData['url'] = $this->helper->getImagePath($image);
        if ($image->getWidth()) {
            $roomTypeImageData['width'] = (int)$image->getWidth();
        }
        if ($image->getHeight()) {
            $roomTypeImageData['height'] = (int)$image->getHeight();
        }

        return $roomTypeImageData;
    }
}
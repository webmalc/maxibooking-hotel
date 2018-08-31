<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Gedmo\Translatable\Document\Translation;
use Gedmo\Translatable\TranslatableListener;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class MultiLangTranslator
{
    private $dm;
    private $helper;
    private $propertyAccessor;
    private $translatableListener;
    private $defaultLocale;

    public function __construct(
        DocumentManager $dm,
        Helper $helper,
        PropertyAccessor $propertyAccessor,
        TranslatableListener $translatableListener,
        string $defaultLocale
    ) {
        $this->dm = $dm;
        $this->helper = $helper;
        $this->propertyAccessor = $propertyAccessor;
        $this->translatableListener = $translatableListener;
        $this->defaultLocale = $defaultLocale;
        $this->translatableListener->setPersistDefaultLocaleTranslation(true);
    }

    /**
     * @param $document
     * @param array $translationsByFields
     */
    public function saveByMultiLanguagesFields($document, array $translationsByFields)
    {
        $repository = $this->dm->getRepository('GedmoTranslatable:Translation');

        foreach ($translationsByFields as $fieldName => $translationsByLanguages) {
            foreach ($translationsByLanguages as $language => $translation) {
                $document->setLocale($language);
                $this->propertyAccessor->setValue($document, $fieldName, $translation);
                $this->dm->flush();
            }

        }

        $this->dm->flush();
    }

    /**
     * @param Base $document
     * @param string $translatableField
     * @param array $languages
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getTranslationsByLanguages(Base $document, string $translatableField, array $languages)
    {
        $result = [
            $this->translatableListener->getListenerLocale() => $this->propertyAccessor->getValue($document, $translatableField)
        ];

        /** @var Translation[] $translations */
        $translations = $this->dm
            ->getRepository('GedmoTranslatable:Translation')
            ->createQueryBuilder()
            ->field('foreignKey')->equals($document->getId())
            ->field('field')->equals($translatableField)
            ->field('locale')->in($languages)
            ->getQuery()
            ->execute()
            ->toArray();

        foreach ($translations as $translation) {
            $result[$translation->getLocale()] = $translation->getContent();
        }

        return $result;
    }
}
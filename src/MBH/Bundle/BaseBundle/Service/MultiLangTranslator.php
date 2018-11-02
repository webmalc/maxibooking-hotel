<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Gedmo\Translatable\Document\Translation;
use Gedmo\Translatable\TranslatableListener;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Translation\TranslatorInterface;

class MultiLangTranslator
{
    private $dm;
    private $helper;
    private $propertyAccessor;
    private $translatableListener;
    private $clientConfigManager;
    private $translator;
    private $defaultLocale;

    public function __construct(
        DocumentManager $dm,
        Helper $helper,
        PropertyAccessor $propertyAccessor,
        TranslatableListener $translatableListener,
        ClientConfigManager $clientConfigManager,
        TranslatorInterface $translator,
        string $defaultLocale
    ) {
        $this->dm = $dm;
        $this->helper = $helper;
        $this->propertyAccessor = $propertyAccessor;
        $this->translatableListener = $translatableListener;
        $this->clientConfigManager = $clientConfigManager;
        $this->translator = $translator;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param $document
     * @param array $translationsByFields
     */
    public function saveByMultiLanguagesFields($document, array $translationsByFields)
    {
        if (!$this->clientConfigManager->hasSingleLanguage()) {
            foreach ($translationsByFields as $fieldName => $translationsByLanguages) {
                foreach ($translationsByLanguages as $language => $translation) {
                    if ($fieldName === 'fullTitle' && $translation === '') {
                        continue;
                    }

                    $this->forceSaveTranslation($document, $fieldName, $language, $translation);
                }
            }

            $this->dm->flush();
        } else {
            foreach ($translationsByFields as $fieldName => $value) {
                $this->forceSaveTranslation($document, $fieldName, $this->defaultLocale, $value);
            }

            if ($this->defaultLocale !== $this->translator->getLocale()) {
                //Set temporarily another default locale for saving field value in document, not in translation unit
                $this->translatableListener->setDefaultLocale($this->translator->getLocale());
                $this->dm->flush();
                $this->translatableListener->setDefaultLocale($this->defaultLocale);
            } else {
                $this->dm->flush();
            }
        }
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
        $this->translatableListener->setPersistDefaultLocaleTranslation(true);
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
        $this->translatableListener->setPersistDefaultLocaleTranslation(false);

        return $result;
    }

    /**
     * @param Base $document
     * @param string $field
     * @param string $locale
     * @param $value
     */
    public function forceSaveTranslation(Base $document, string $field, string $locale, $value)
    {
        //set temporarily "persistDefaultLocaleTranslation" param for saving field values in translation units
        $repo = $this->dm->getRepository('GedmoTranslatable:Translation');
        $this->translatableListener->setPersistDefaultLocaleTranslation(true);
        $repo->translate($document, $field, $locale, $value);
        $this->translatableListener->setPersistDefaultLocaleTranslation(false);
    }
}
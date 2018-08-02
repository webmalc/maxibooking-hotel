<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Gedmo\Translatable\Document\Translation;
use MBH\Bundle\BaseBundle\Document\Base;

class MultiLangTranslator
{
    private $dm;
    private $helper;

    public function __construct(DocumentManager $dm, Helper $helper) {
        $this->dm = $dm;
        $this->helper = $helper;
    }

    /**
     * @param $document
     * @param array $translationsByFields
     * @param bool $withFlush
     */
    public function saveByMultiLanguagesFields($document, array $translationsByFields, $withFlush = true)
    {
        $repository = $this->dm->getRepository('GedmoTranslatable:Translation');

        foreach ($translationsByFields as $fieldName => $translationsByLanguages) {
            foreach ($translationsByLanguages as $language => $translation) {
                $repository->translate($document, $fieldName, $language, $translation);
            }
        }

        if ($withFlush) {
            $this->dm->flush();
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
        $result = [];
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
<?php

namespace MBH\Bundle\ClientBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;

/**
 * Created by PhpStorm.
 * User: danya
 * Date: 27.06.17
 * Time: 15:15
 */
class DocumentTemplateData extends AbstractFixture implements OrderedFixtureInterface
{
    const LOCALE_RU = 'ru';
    const LOCALE_COM = 'com';

    const DOCUMENT_TEMPLATE_DATA = [
        self::LOCALE_RU => [
            'Акт' => 'act',
            'Счет' => 'bill',
            'Подтверждение' => 'confirmation',
            'Свидетельство о регистрации по месту пребывания' => 'evidence',
            'Анкета ФМС (Форма 5)' => 'fms_form_5',
            'Анкета (Форма 1-Г)' => 'form_1_g',
            'Расписка' => 'receipt',
            'Регистрационная карта' => 'registration_card'
        ],
        self::LOCALE_COM => [
            'Invoice' => 'en_invoice'
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $locale = $this->container->getParameter('locale') === self::LOCALE_RU ? self::LOCALE_RU : self::LOCALE_COM;
        foreach (self::DOCUMENT_TEMPLATE_DATA[$locale] as $name => $templateFile) {
            $filePath = $this->container->get('kernel')->getRootDir()
                . '/../src/MBH/Bundle/PackageBundle/Resources/views/Documents/pdfTemplates/'
                . $templateFile
                . '.html.twig';

            $content = file_get_contents($filePath);
            $template = (new DocumentTemplate())
                ->setTitle($name)
                ->setContent($content)
                ->setIsDefault(true);
            $manager->persist($template);
            $this->setReference($templateFile . 'Template', $template);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 9999;
    }

    public function getEnvs(): array
    {
        return ['test', 'dev', 'sandbox', 'prod'];
    }
}
<?php

namespace MBH\Bundle\ClientBundle\DataFixtures;

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
    const DOCUMENT_TEMPLATE_DATA = [
        'Акт' => 'act',
        'Счет' => 'bill',
        'Подтверждение' => 'confirmation',
        'Подтверждение(EN)' => 'confirmation_en',
        'Свидетельство о регистрации по месту пребывания' => 'evidence',
        'Анкета ФМС (Форма 5)' => 'fms_form_5',
        'Анкета (Форма 1-Г)' => 'form_1_g',
        'Расписка' => 'receipt',
        'Регистрационная карта' => 'registration_card'
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        //TODO: Убрать
        $templates = $manager->getRepository('MBHClientBundle:DocumentTemplate')->findAll();
        foreach ($templates as $template) {
            $manager->remove($template);
        }
        $manager->flush();
        foreach (self::DOCUMENT_TEMPLATE_DATA as $name => $templateFile) {
            $filePath = $this->container->get('kernel')->getRootDir()
                . '/../src/MBH/Bundle/PackageBundle/Resources/views/Documents/pdfTemplates/'
                . $templateFile
                .'.html.twig';

            $content = file_get_contents($filePath);
            $template = (new DocumentTemplate())
                ->setTitle($name)
                ->setContent($content);
            $manager->persist($template);
            $this->setReference($templateFile . 'Template', $template);
        }

        $manager-> flush();
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
}
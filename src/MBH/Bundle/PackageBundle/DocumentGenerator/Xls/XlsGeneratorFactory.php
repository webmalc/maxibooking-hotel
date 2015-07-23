<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Xls;


use MBH\Bundle\PackageBundle\DocumentGenerator\GeneratorFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilder;

/**
 * Class DocumentXlsGeneratorFactory
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class XlsGeneratorFactory implements GeneratorFactoryInterface
{
    const TYPE_NOTICE = 'xls_notice';

    protected $container;

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return [
            self::TYPE_NOTICE
        ];
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param $type
     * @return \Symfony\Component\Form\Form|null
     */
    public function createFormByType($type)
    {
        if ($type == self::TYPE_NOTICE) {
            /** @var FormBuilder $formBuilder */
            $formBuilder = $this->container->get('form.factory')->createBuilder('form');

            $formBuilder->add('tourist', 'document', [
                'required' => true,
                'class' => 'MBH\Bundle\PackageBundle\Document\Tourist',
                'label' => 'form.task.tourist'
            ]);

            return $formBuilder->getForm();
        }

        return null;
    }

    /**
     * @param $type
     * @return bool
     */
    public function hasForm($type)
    {
        return $type == self::TYPE_NOTICE;
    }


    public function createGeneratorByType($type)
    {
        if($type == self::TYPE_NOTICE) {
            $generator = new NoticeStayPlaceXlsGenerator();
            $generator->setContainer($this->container);
            return $generator;
        }
        throw new \InvalidArgumentException();
    }
}
<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Xls;


use MBH\Bundle\PackageBundle\DocumentGenerator\DocumentResponseGeneratorInterface;
use MBH\Bundle\PackageBundle\DocumentGenerator\GeneratorFactoryInterface;
use MBH\Bundle\PackageBundle\DocumentGenerator\Xls\Type\NoticeStayPlaceXlsType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DocumentXlsGeneratorFactory
 *

 */
class XlsGeneratorFactory implements GeneratorFactoryInterface
{
    const TYPE_NOTICE = 'xls_notice';

    /**
     * @var ContainerInterface
     */
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
     * @param $options
     * @return \Symfony\Component\Form\Form|null
     */
    public function createFormByType($type, $options = [])
    {
        if ($type == self::TYPE_NOTICE) {
            $dm = $this->container->get('doctrine_mongodb')->getManager();
            return $this->container->get('form.factory')->create(new NoticeStayPlaceXlsType($dm), null, $options);
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

    /**
     * @param string $type
     * @return DocumentResponseGeneratorInterface
     */
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
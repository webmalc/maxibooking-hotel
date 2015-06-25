<?php

namespace MBH\Bundle\BaseBundle\Form\Extension;


use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CityType
 * @package MBH\Bundle\BaseBundle\Form\Extension
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class CityType extends AbstractType
{
    private $documentManager;

    /**
     * @var Container
     */
    //private $container;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->documentManager = $managerRegistry->getManager();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->setCompound(true);

        $builder->addModelTransformer(new EntityToIdTransformer($this->documentManager,
            'MBHHotelBundle:City'
        ));
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'mbh_city';
    }
}
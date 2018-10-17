<?php
/**
 * Created by PhpStorm.
 * Date: 12.10.18
 */

namespace MBH\Bundle\PriceBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffCombinationHolder;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TariffCombinationFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {

                /** @var PersistentCollection $persistentCollection */
                $persistentCollection = $event->getData();
                $useIds = [];
                /** @var TariffCombinationHolder $data */
                foreach ($persistentCollection as $data) {
                    if ($data->getCombinationTariffId() !== null && !isset($useIds[$data->getCombinationTariffId()])) {
                        $useIds[$data->getCombinationTariffId()] = true;
                    } else {
                        $persistentCollection->removeElement($data);
                    }
                }
            }
        );

        parent::buildForm($builder, $options);
    }

    public function getParent()
    {
        return CollectionType::class;
    }

}
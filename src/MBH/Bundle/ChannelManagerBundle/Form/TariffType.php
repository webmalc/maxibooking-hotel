<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;

class TariffType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $options['entity'];
        $tariffs = $config->getHotel()->getTariffs();
        $tariffEntities = $config->getTariffs();
        $ids = [];

        foreach ($tariffEntities as $tariff) {
            $ids[$tariff->getTariff()->getId()] = $tariff->getTariffId();
        }

        foreach ($tariffs as $tariff) {

            if (!$tariff->getIsOnline()) {
                continue;
            }

            (isset($ids[$tariff->getId()])) ? $data = $ids[$tariff->getId()] : $data = null;

            if ($tariff->getIsDefault()) {
                $type = 'hidden';
                $data = 0;
            }  else {
                $type = 'text';
            }

            $builder
                ->add($tariff->getId(), $type, [
                        'label' => $tariff->getName(),
                        'required' => false,
                        'attr' => [
                            'placeholder' => 'ID тарифа <'. $tariff->getName() .'> в настройках сервиса',
                        ],
                        'data' => $data
                    ])
            ;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'entity' => false
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_channelmanagerbundle_tariff_type';
    }

}

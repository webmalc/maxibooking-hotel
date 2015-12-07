<?php

namespace MBH\Bundle\CashBundle\Form\Extension;

use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PayerType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class PayerType extends AbstractType
{
    /**
     * @param PayerInterface[] $payers
     * @return array
     * @throws \Exception
     */
    protected function choicesFromPayers($payers)
    {
        $result = [];
        foreach ($payers as $payer) {
            $text = $payer->getName();
            if ($payer instanceof Organization) {
                $text .= ' (ИНН ' . $payer->getInn() . ') ' . $payer->getDirectorFio();
            } elseif ($payer instanceof Tourist) {
                $text .= $payer->getBirthday() ? ' ' . $payer->getBirthday()->format('d.m.Y') : '';
            } else {
                throw new \Exception();
            }

            $result[$payer->getId()] = $text;
        }
        return $result;
    }

    protected function choicesAttrFromPayers($payers)
    {
        $result = [];
        foreach ($payers as $payer) {
            if ($payer instanceof Organization) {
                $prefix = 'organization';
            } elseif ($payer instanceof Tourist) {
                $prefix = 'tourist';
            } else {
                throw new \Exception();
            }

            $result[$payer->getId()] = ['data-type' => $prefix];
        }
        return $result;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $uniqud = uniqid();

        $options['ajax'] = 0;
        $tourist = new Tourist();
        $tourist->setFirstName('11');

        $reflectionClass = new \ReflectionClass(Tourist::class);
        $prop = $reflectionClass->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($tourist, '1111');

        $tourist->setBirthday(new \DateTime());
        $organization = new Organization();
        $organization->setName('22');
        $organization->setInn('22312');

        $reflectionClass = new \ReflectionClass(Organization::class);
        $prop = $reflectionClass->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($organization, '2222');
        $options['payers'] = [$tourist, $organization];

        /*dump($this->choicesFromPayers($options['payers']));
        dump($this->choicesAttrFromPayers($options['payers']));*/

        $builder
            ->add('select', 'choice', [
                'label' => 'form.cashDocumentType.payer',
                'required' => true,
                'mapped' => false,
                'data' => $options['payer'] ? $options['payer'] : null,
                'group' => $options['groupName'],
                'choices' => $this->choicesFromPayers($options['payers']),
                'choice_attr' => $this->choicesAttrFromPayers($options['payers']),
                'empty_value' => '',
                'attr' => [
                    'placeholder' => 'form.cashDocumentType.placeholder_fio',
                    'class' => 'select-payer'.($options['ajax'] ? ' plain-html' : ''),
                    'style' => 'min-width: 500px',
                    'data-ajax' => (int) $options['ajax'],
                    'data-uniqud' => $uniqud,
                ],
            ])
            ->add('organization', 'hidden', [
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'organization-hidden',
                    'data-uniqud' => $uniqud,
                ]
            ])
            ->add('tourist', 'hidden', [
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'tourist-hidden',
                    'data-uniqud' => $uniqud,
                ]
            ])
        ;
    }

    public function getName()
    {
        return 'mbh_cash_payer';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'ajax' => false,
            'payers' => [],
            'payer' => null,
            'groupName' => null,//todo remove,
            'compound' => true
        ]);
    }
}
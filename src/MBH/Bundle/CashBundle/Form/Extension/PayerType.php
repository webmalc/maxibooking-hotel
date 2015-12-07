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
                $prefix = 'org';
                $text .= ' (ИНН ' . $payer->getInn() . ') ' . $payer->getDirectorFio();
            } elseif ($payer instanceof Tourist) {
                $prefix = 'tourist';
                $text .= $payer->getBirthday() ? ' ' . $payer->getBirthday()->format('d.m.Y') : '';
            } else {
                throw new \Exception();
            }

            $result[$prefix . '_' . $payer->getId()] = $text;
        }
        return $result;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('select', 'choice', [
                'label' => 'form.cashDocumentType.payer',
                'required' => true,
                'mapped' => false,
                'data' => $options['payer'] ? $options['payer'] : null,
                'group' => $options['groupName'],
                'choices' => $this->choicesFromPayers($options['payers']),
                'attr' => [
                    'placeholder' => 'form.cashDocumentType.placeholder_fio',
                    'class' => 'select-payer plain-html',
                    'style' => 'min-width: 500px',
                    'data-ajax' => (int) $options['ajax']
                ],
                'empty_value' => ''
            ])
            ->add('organization', 'hidden', [
                'required' => false,
                'mapped' => false,
            ])
            ->add('tourist', 'hidden', [
                'required' => false,
                'mapped' => false,
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
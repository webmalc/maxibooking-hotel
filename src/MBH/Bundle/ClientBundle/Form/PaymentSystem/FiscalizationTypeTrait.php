<?php
/**
 * Created by PhpStorm.
 * Date: 30.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\ExtraData;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\FiscalizationInterface;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\TaxMapInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Trait FiscalizationTypeTrait
 * @package MBH\Bundle\ClientBundle\Form\PaymentSystem
 * @property ExtraData $extraData
 */
trait FiscalizationTypeTrait
{
    protected function addFieldsForFiscalization(
        FormBuilderInterface $builder,
        TaxMapInterface $doc = null,
        bool $fieldTaxationRateCode = true,
        bool $fieldTaxationSystemCode = true
    ): void
    {
        $formModifier = function (
            FormEvent $event,
            TaxMapInterface $doc = null,
            bool $fieldTaxationRateCode = true,
            bool $fieldTaxationSystemCode = true
        ) {

            /** @var FormBuilderInterface $form */
            $form = $event->getForm();
            if ($event->getData() instanceof FiscalizationInterface) {
//                $disabledTaxion = !$event->getData()->isWithFiscalization();
                $disabledTaxion = false;
                $attr = [];
            } else {
                $disabledTaxion = empty($event->getData()['isWithFiscalization']);
                $attr = [
                    'disabled' => $disabledTaxion,
                ];
            }

            $attr['class'] = 'select_tax_code';

            $form->add(
                    'isWithFiscalization',
                    CheckboxType::class,
                    $this->addCommonAttributes(
                        [
                            'label' => 'form.clientPaymentSystemType.is_with_fiscalization.label',
                            'attr'  => [
                                'class' => 'checkboxForIsWithFiscalization',
                                'disabled' => $disabledTaxion,
                            ],
                        ]
                    )
                );

            if ($fieldTaxationRateCode) {
                $form->add(
                    'taxationRateCode',
                    InvertChoiceType::class,
                    $this->addCommonAttributes(
                        [
                            'label'   => 'form.clientPaymentSystemType.taxation_rate_code',
                            'choices' => $this->extraData->getTaxationRateCodes($doc),
                            'attr'    => $attr
                        ]
                    )
                );
            }

            if ($fieldTaxationSystemCode) {
                $form->add(
                    'taxationSystemCode',
                    InvertChoiceType::class,
                    $this->addCommonAttributes(
                        [
                            'label'   => 'form.clientPaymentSystemType.taxation_system_code',
                            'choices' => $this->extraData->getTaxationSystemCodes($doc),
                            'attr'    => $attr
                        ]
                    )
                );
            }
        };


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier, $doc, $fieldTaxationRateCode, $fieldTaxationSystemCode) {
                $formModifier($event, $doc, $fieldTaxationRateCode, $fieldTaxationSystemCode);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier, $doc, $fieldTaxationRateCode, $fieldTaxationSystemCode) {
                $formModifier($event, $doc, $fieldTaxationRateCode, $fieldTaxationSystemCode);
            }
        );
    }
}
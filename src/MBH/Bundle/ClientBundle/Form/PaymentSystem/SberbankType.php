<?php
/**
 * Created by PhpStorm.
 * Date: 29.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\ClientBundle\Document\PaymentSystem\Sberbank;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotNull;

class SberbankType extends PaymentSystemType
{
    use ExtraDataTrait;
    use FiscalizationTypeTrait;

    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new Sberbank();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sberbank = $builder->getData();

        $formModifier = function (FormEvent $event, string $eventName) {

            $disabledUserAndPass = false;
            $disabledToken = false;

            if ($eventName === FormEvents::PRE_SUBMIT) {
                if (empty($event->getData()['token'])) {
                    $disabledToken = true;
                } else {
                    $disabledUserAndPass = true;
                }
            }

            $form = $event->getForm();

            $form->add(
                'userName',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.sberbank_userName',
                        'help'  => 'При указании token, userName и password не обязателен.',
                        'attr'  => [
                            'disabled' => $disabledUserAndPass,
                            'class' => 'sberbank-field-userName'
                        ],
                    ]
                )
            )
                ->add(
                    'password',
                    TextType::class,
                    $this->addCommonAttributes(
                        [
                            'label' => 'form.clientPaymentSystemType.sberbank_password',
                            'help'  => 'При указании token, userName и password не обязателен.',
                            'attr'  => [
                                'disabled' => $disabledUserAndPass,
                                'class' => 'sberbank-field-password'
                            ],
                        ]
                    )
                )
                ->add(
                    'token',
                    TextType::class,
                    $this->addCommonAttributes(
                        [
                            'label' => 'form.clientPaymentSystemType.sberbank_token',
                            'help'  => 'При указании userName и password, token не обязателен.',
                            'attr'  => [
                                'disabled' => $disabledToken,
                                'class' => 'sberbank-field-token'
                            ],
                        ]
                    )
                );
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $formEvent) use ($formModifier) {
                $formModifier($formEvent, FormEvents::PRE_SET_DATA);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $formEvent) use ($formModifier) {
                $formModifier($formEvent, FormEvents::PRE_SUBMIT);
            }
        );


        $builder
            ->add(
                'sessionTimeoutMinutes',
                NumberType::class,
                $this->addCommonAttributes(
                    [
                        'label'       => 'form.clientPaymentSystemType.sberbank_sessionTimeoutMinutes',
                        'constraints' => [new \Symfony\Component\Validator\Constraints\Range(['min' => 1])],
                    ]
                )
            );

        $this->addFieldsForFiscalization($builder, $sberbank);
    }
}
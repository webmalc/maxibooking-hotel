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

class SberbankType extends PaymentSystemType
{
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
                        'label' => 'form.clientPaymentSystemType.sberbank.userName.label',
                        'help'  => 'form.clientPaymentSystemType.sberbank.userName.help',
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
                            'label' => 'form.clientPaymentSystemType.sberbank.password.label',
                            'help'  => 'form.clientPaymentSystemType.sberbank.password.help',
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
                            'label' => 'form.clientPaymentSystemType.sberbank.token.label',
                            'help'  => 'form.clientPaymentSystemType.sberbank.token.help',
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
                        'label'       => 'form.clientPaymentSystemType.sberbank.sessionTimeoutMinutes.label',
                        'help'        => 'form.clientPaymentSystemType.sberbank.sessionTimeoutMinutes.help',
                        'constraints' => [new \Symfony\Component\Validator\Constraints\Range(['min' => 1])],
                    ]
                )
            );

        $urlAttr = [
            'group'       => 'no-group',
            'mapped' => false,
            'required' => false,
            'attr' => [
                'disabled' => true
            ]
        ];

        $urlSuccessAttr = [
            'label' => 'orm.clientPaymentSystemType.sberbank.successUrl.label',
            'data' => $this->getClientConfig()->getSuccessUrl()
            ];

        $urlFailAttr = [
            'label' => 'orm.clientPaymentSystemType.sberbank.failUrl.label',
            'data' => $this->getClientConfig()->getFailUrl()
            ];

        $urlWarning = [
            'help' => 'Необходимо указать url, иначе ссылка будет сформерована на строне Sberbank\'а.',
            'attr' => [
                'style' => 'border-color: red;'
                ]
        ];

        if ($this->getClientConfig()->getSuccessUrl() === null) {
            $urlSuccessAttr = array_merge($urlSuccessAttr, $urlWarning);
        }

        if ($this->getClientConfig()->getFailUrl() === null) {
            $urlFailAttr = array_merge($urlFailAttr, $urlWarning);
        }

        $builder
            ->add(
                'successUrl',
                TextType::class,
                array_merge_recursive($urlAttr, $urlSuccessAttr)
            )
            ->add(
                'failUrl',
                TextType::class,
                array_merge_recursive($urlAttr, $urlFailAttr)
            );

        $this->addFieldsForFiscalization($builder, $sberbank);
    }
}
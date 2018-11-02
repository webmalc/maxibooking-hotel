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
use Symfony\Component\Validator\Constraints\Url;

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

            $form = $event->getForm();
            if ($form->isSubmitted()) {
                return;
            }

            $backendDisabledUserAndPass = false;
            $backendDisabledToken = false;
            $frontendRequiredUserAndPass = true;
            $frontendRequiredToken = true;

            $disabledFailUrl = false;

            /** выключение проверки на backend`е */
            if ($eventName === FormEvents::PRE_SUBMIT) {
                if (empty($event->getData()['token'])) {
                    $backendDisabledToken = true;
                } else {
                    $backendDisabledUserAndPass = true;
                }

                $disabledFailUrl = empty($event->getData()['failUrl']);
            }

            if ($event->getData() instanceof Sberbank) {
                /** @var Sberbank $sbrf */
                $sbrf = $event->getData();

                $returnUrl = $sbrf->getReturnUrl() ?? $this->getClientConfig()->getSuccessUrl();
                $failUrl = $sbrf->getFailUrl() ?? $this->getClientConfig()->getFailUrl();

                /** выключение проверки на frontend`е */
                $emptyToken = $sbrf->getToken() === null;
                $emptyRequiredUserAndPass = $sbrf->getUserName() === null || $sbrf->getPassword() === null;

                if (!$emptyToken) {
                    $frontendRequiredUserAndPass = false;
                } elseif ($emptyToken && !$emptyRequiredUserAndPass) {
                    $frontendRequiredToken = false;
                }
            }

            $addClass = function (string $name, bool $add) {
                return $name . ($add ? ' payment-system-form_sberbank' : '');
            };

            // поля: токен, юзер, пассворд
            $form->add(
                'userName',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.sberbank.userName.label',
                        'help'  => 'form.clientPaymentSystemType.sberbank.userName.help',
                        'attr'  => [
                            'disabled' => $backendDisabledUserAndPass,
                            'class'    => $addClass('sberbank-field-userName', $frontendRequiredUserAndPass),
                        ],
                    ],
                    $frontendRequiredUserAndPass
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
                                'disabled' => $backendDisabledUserAndPass,
                                'class'    => $addClass('sberbank-field-password', $frontendRequiredUserAndPass),
                            ],
                        ],
                        $frontendRequiredUserAndPass
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
                                'disabled' => $backendDisabledToken,
                                'class'    => $addClass('sberbank-field-token', $frontendRequiredToken),
                            ],
                        ],
                        $frontendRequiredToken
                    )
                );

            // поля: returnUrl, failUrl

            $optionFailUrl = [
                'label' => 'form.clientPaymentSystemType.sberbank.failUrl.label',
                'help'  => 'form.clientPaymentSystemType.sberbank.failUrl.help',
                'data'  => $failUrl ?? null,
            ];

            if ($disabledFailUrl) {
                $optionFailUrl['attr'] = ['disabled' => true];
            } else {
                $optionFailUrl['constraints'] = [new Url(['protocols' => ['https']])];
            }

            $form
                ->add(
                    'returnUrl',
                    TextType::class,
                    $this->addCommonAttributes([
                        'label' => 'form.clientPaymentSystemType.sberbank.returnUrl.label',
                        'help'  => 'form.clientPaymentSystemType.sberbank.returnUrl.help',
                        'data' => $returnUrl ?? null,
                        'constraints' => [new Url(['protocols' => ['https']])]
                    ])
                )
                ->add(
                    'failUrl',
                    TextType::class,
                    $this->addCommonAttributes($optionFailUrl, false)
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

        $builder
            ->add(
                'securityKey',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.sberbank.securityKey.label',
                        'help'  => 'form.clientPaymentSystemType.sberbank.securityKey.help',
                    ]
                )
            );

        $this->addFieldsForFiscalization($builder, $sberbank);
    }
}
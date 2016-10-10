<?php

namespace MBH\Bundle\ClientBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientPaymentSystemType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['entity'];
        $robokassaMerchantLogin = $robokassaMerchantPass1 = $robokassaMerchantPass2 = null;
        $payanywayMntId = $payanywayKey = null;
        $moneymailShopIDP = $moneymailKey = null;
        $unitellerShopIDP = $unitellerPassword = null;
        $rbkEshopId = $rbkSecretKey = null;
        $paypalLogin = $paypalSecretKey = null;
        $default = $options['default'];

        if ($entity) {
            $robokassaMerchantLogin = $entity->getRobokassa() ? $entity->getRobokassa()->getRobokassaMerchantLogin() : '';
            $robokassaMerchantPass1 = $entity->getRobokassa() ? $entity->getRobokassa()->getRobokassaMerchantPass1() : '';
            $robokassaMerchantPass2 = $entity->getRobokassa() ? $entity->getRobokassa()->getRobokassaMerchantPass2() : '';
            $payanywayMntId = $entity->getPayanyway() ? $entity->getPayanyway()->getPayanywayMntId() : '';
            $payanywayKey = $entity->getPayanyway() ? $entity->getPayanyway()->getPayanywayKey() : '';
            $moneymailShopIDP = $entity->getMoneymail() ? $entity->getMoneymail()->getMoneymailShopIDP() : '';
            $moneymailKey = $entity->getMoneymail() ? $entity->getMoneymail()->getMoneymailKey() : '';
            $unitellerShopIDP = $entity->getUniteller() ? $entity->getUniteller()->getUnitellerShopIDP() : '';
            $unitellerPassword = $entity->getUniteller() ? $entity->getUniteller()->getUnitellerPassword() : '';
            $rbkEshopId = $entity->getRbk() ? $entity->getRbk()->getRbkEshopId() : '';
            $rbkSecretKey = $entity->getRbk() ? $entity->getRbk()->getRbkSecretKey() : '';
            $paypalLogin = $entity->getPaypal() ? $entity->getPaypal()->getPaypalLogin() : '';
            $paypalSecretKey = $entity->getPaypal() ? $entity->getPaypal()->getPaypalSecretKey() : '';

            if ($entity->getPaymentSystem()) {
                $default = $entity->getPaymentSystem();
            }
        }

        if (!$options['change']) {
            $builder
                ->add(
                    'paymentSystem',
                    'hidden',
                    [
                        'data' => $default,
                    ]
                );
        } else {
            $builder
                ->add(
                    'paymentSystem',
                    'choice',
                    [
                        'label' => 'form.clientPaymentSystemType.payment_system',
                        'choices' => $options['paymentTypes'],
                        'group' => 'form.clientPaymentSystemType.payment_system_group',
                        'empty_value' => '',
                        'data' => $default,
                        'required' => true
                    ]
                );
        }
        $builder
            ->add(
                'robokassaMerchantLogin',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.shop_login',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params robokassa'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $robokassaMerchantLogin
                ]
            )
            ->add(
                'robokassaMerchantPass1',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.password_one',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params robokassa'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $robokassaMerchantPass1
                ]
            )
            ->add(
                'robokassaMerchantPass2',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.password_two',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params robokassa'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $robokassaMerchantPass2
                ]
            )
            ->add(
                'payanywayMntId',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.extended_account_number',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params payanyway'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $payanywayMntId
                ]
            )
            ->add(
                'payanywayKey',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.data_integrity_code',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params payanyway'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $payanywayKey
                ]
            )
            ->add(
                'moneymailShopIDP',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.moneymail_shop_id',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params moneymail'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $moneymailShopIDP
                ]
            )
            ->add(
                'moneymailKey',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.moneymail_key',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params moneymail'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $moneymailKey
                ]
            )
            ->add(
                'unitellerShopIDP',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.uniteller_shop_id',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params uniteller'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $unitellerShopIDP
                ]
            )
            ->add(
                'unitellerPassword',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.uniteller_password',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params uniteller'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $unitellerPassword
                ]
            )
            ->add(
                'rbkEshopId',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.rbk_eshop_id',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params rbk'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $rbkEshopId
                ]
            )
            ->add(
                'rbkSecretKey',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.rbk_secret_key',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params rbk'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $rbkSecretKey
                ]
            )
            ->add(
                'paypalLogin',
                'text',
                [
                    'label' => 'Login',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params paypal'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $paypalLogin
                ]
            )
            ->add(
                'paypalSecretKey',
                'text',
                [
                    'label' => 'key',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params paypal'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $paypalSecretKey
                ]
            )
            ->add(
                'successUrl',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.successUrl',
                    'help' => 'form.clientPaymentSystemType.successUrlDesc',
                    'group' => 'form.clientPaymentSystemType.payment_system_group_links',
                    'required' => false,
                ]
            )
            ->add(
                'failUrl',
                'text',
                [
                    'label' => 'form.clientPaymentSystemType.failUrl',
                    'help' => 'form.clientPaymentSystemType.failUrlDesc',
                    'group' => 'form.clientPaymentSystemType.payment_system_group_links',
                    'required' => false,
                ]
            )
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\ClientBundle\Document\ClientConfig',
            'paymentTypes' => [],
            'entity' => null,
            'default' => null,
            'change' => false
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_clientbundle_client_payment_system_type';
    }

}

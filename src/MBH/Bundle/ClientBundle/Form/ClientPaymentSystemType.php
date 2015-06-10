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
        $default = $options['default'];

        if ($entity) {
            $robokassaMerchantLogin = $entity->getRobokassa() ? $entity->getRobokassa()->getRobokassaMerchantLogin() : '';
            $robokassaMerchantPass1 = $entity->getRobokassa() ? $entity->getRobokassa()->getRobokassaMerchantPass1() : '';
            $robokassaMerchantPass2 = $entity->getRobokassa() ? $entity->getRobokassa()->getRobokassaMerchantPass2() : '';
            $payanywayMntId = $entity->getPayanyway() ? $entity->getPayanyway()->getPayanywayMntId() : '';
            $payanywayKey = $entity->getPayanyway() ? $entity->getPayanyway()->getPayanywayKey() : '';
            $moneymailShopIDP = $entity->getMoneymail() ? $entity->getMoneymail()->getMoneymailShopIDP() : '';
            $moneymailKey = $entity->getMoneymail() ? $entity->getMoneymail()->getMoneymailKey() : '';
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
                    'mapped' => false,
                    'data' => $moneymailKey
                ]
            );

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

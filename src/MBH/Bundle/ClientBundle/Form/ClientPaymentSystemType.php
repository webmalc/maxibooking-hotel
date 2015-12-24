<?php

namespace MBH\Bundle\ClientBundle\Form;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ClientPaymentSystemType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class ClientPaymentSystemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ClientConfig $entity */
        $entity = $options['entity'];
        $robokassaMerchantLogin = $robokassaMerchantPass1 = $robokassaMerchantPass2 = null;
        $payanywayMntId = $payanywayKey = null;
        $moneymailShopIDP = $moneymailKey = null;
        $unitellerShopIDP = $unitellerPassword = null;
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

            $yandexmoneyPassword = null;
            $yandexmoneyShopId = null;
            $yandexmoneyscid = null;

            if ($entity->getYandexMoney()) {
                $yandexmoneyPassword = $entity->getYandexMoney()->getYandexmoneypassword();
                $yandexmoneyShopId = $entity->getYandexMoney()->getYandexmoneyshopId();
                $yandexmoneyscid = $entity->getYandexMoney()->getYandexmoneyscid();
            }

            if ($entity->getPaymentSystem()) {
                $default = $entity->getPaymentSystem();
            }
        }

        if (!$options['change']) {
            $builder->add('paymentSystem', 'hidden', ['data' => $default]);
        } else {
            $builder
                ->add('paymentSystem', 'choice', [
                    'label' => 'form.clientPaymentSystemType.payment_system',
                    'required' => true,
                    'choices' => $options['paymentTypes'],
                    'empty_value' => '',
                    ///'data' => $default,
                ]);
        }
        $builder
            ->add('robokassaMerchantLogin', 'text', [
                'label' => 'form.clientPaymentSystemType.shop_login',
                'required' => false,
                'attr' => ['class' => 'payment-system-params robokassa'],
                'mapped' => false,
                'data' => $robokassaMerchantLogin
            ])
            ->add('robokassaMerchantPass1', 'text', [
                'label' => 'form.clientPaymentSystemType.password_one',
                'required' => false,
                'attr' => ['class' => 'payment-system-params robokassa'],
                'mapped' => false,
                'data' => $robokassaMerchantPass1
            ])
            ->add('robokassaMerchantPass2', 'text', [
                'label' => 'form.clientPaymentSystemType.password_two',
                'required' => false,
                'attr' => ['class' => 'payment-system-params robokassa'],
                'mapped' => false,
                'data' => $robokassaMerchantPass2
            ])
            ->add('payanywayMntId', 'text', [
                'label' => 'form.clientPaymentSystemType.extended_account_number',
                'required' => false,
                'attr' => ['class' => 'payment-system-params payanyway'],
                'mapped' => false,
                'data' => $payanywayMntId
            ])
            ->add('payanywayKey', 'text', [
                'label' => 'form.clientPaymentSystemType.data_integrity_code',
                'required' => false,
                'attr' => ['class' => 'payment-system-params payanyway'],
                'mapped' => false,
                'data' => $payanywayKey
            ])
            ->add('moneymailShopIDP', 'text', [
                'label' => 'form.clientPaymentSystemType.moneymail_shop_id',
                'required' => false,
                'attr' => ['class' => 'payment-system-params moneymail'],
                'mapped' => false,
                'data' => $moneymailShopIDP
            ])
            ->add('moneymailKey', 'text', [
                'label' => 'form.clientPaymentSystemType.moneymail_key',
                'required' => false,
                'attr' => ['class' => 'payment-system-params moneymail'],
                'mapped' => false,
                'data' => $moneymailKey
            ])
            ->add('unitellerShopIDP', 'text', [
                'label' => 'form.clientPaymentSystemType.uniteller_shop_id',
                'required' => false,
                'attr' => ['class' => 'payment-system-params uniteller'],
                'mapped' => false,
                'data' => $unitellerShopIDP
            ])
            ->add('unitellerPassword', 'text', [
                'label' => 'form.clientPaymentSystemType.uniteller_password',
                'required' => false,
                'attr' => ['class' => 'payment-system-params uniteller'],
                'mapped' => false,
                'data' => $unitellerPassword
            ])
            ->add('yandexmoneyPassword', 'text', [
                'label' => 'form.clientPaymentSystemType.yandexmoney_password',
                'required' => false,
                'attr' => ['class' => 'payment-system-params yandexmoney'],
                'mapped' => false,
                'data' => $yandexmoneyPassword
            ])
            ->add('yandexmoneyShopId', 'text', [
                'label' => 'form.clientPaymentSystemType.yandexmoney_shopId',
                'required' => false,
                'attr' => ['class' => 'payment-system-params yandexmoney'],
                'mapped' => false,
                'data' => $yandexmoneyShopId
            ])
            ->add('yandexmoneyscid', 'text', [
                'label' => 'form.clientPaymentSystemType.yandexmoney_scid',
                'required' => false,
                'attr' => ['class' => 'payment-system-params yandexmoney'],
                'mapped' => false,
                'data' => $yandexmoneyscid
            ])
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

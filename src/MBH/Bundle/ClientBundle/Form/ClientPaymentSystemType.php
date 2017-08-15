<?php

namespace MBH\Bundle\ClientBundle\Form;

use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientPaymentSystemType extends AbstractType
{
    private $paymentSystems;
    private $paymentSystemsChange;
    private $paymentSystemsDefault;
    private $taxationRateCodes;

    public function __construct($paymentSystems, $paymentSystemsChange, $paymentSystemsDefault, $taxationRateCodes) {
        $this->paymentSystems = $paymentSystems;
        $this->paymentSystemsChange = $paymentSystemsChange;
        $this->paymentSystemsDefault = $paymentSystemsDefault;
        $this->taxationRateCodes = $taxationRateCodes;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ClientConfig $entity */
        $entity = $options['entity'];
        $robokassaMerchantLogin = $robokassaMerchantPass1 = $robokassaMerchantPass2 = null;
        $payanywayMntId = $payanywayKey = null;
        $moneymailShopIDP = $moneymailKey = null;
        $unitellerIsWithFiscalization = $unitellerShopIDP = $unitellerPassword = $taxationSystemCode = $taxationRateCode = null;
        $rbkEshopId = $rbkSecretKey = null;
        $paypalLogin = null;
        $default = $this->paymentSystemsDefault;

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
            $unitellerIsWithFiscalization = $entity->getUniteller() ? $entity->getUniteller()->isWithFiscalization(): '';
            $taxationRateCode = $entity->getUniteller() ? $entity->getUniteller()->getTaxationRateCode() : '';
            $taxationSystemCode = $entity->getUniteller() ? $entity->getUniteller()->getTaxationSystemCode() : '';
            $rbkEshopId = $entity->getRbk() ? $entity->getRbk()->getRbkEshopId() : '';
            $rbkSecretKey = $entity->getRbk() ? $entity->getRbk()->getRbkSecretKey() : '';
            $paypalLogin = $entity->getPaypal() ? $entity->getPaypal()->getPaypalLogin() : '';

            if ($entity->getPaymentSystem()) {
                $default = $entity->getPaymentSystem();
            }
        }

        if (!$this->paymentSystemsChange) {
            $builder
                ->add(
                    'paymentSystem',
                    HiddenType::class,
                    [
                        'data' => $default,
                    ]
                );
        } else {
            $builder
                ->add(
                    'paymentSystem',
                    \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class,
                    [
                        'label' => 'form.clientPaymentSystemType.payment_system',
                        'choices' => $this->paymentSystems,
                        'group' => 'form.clientPaymentSystemType.payment_system_group',
                        'placeholder' => '',
                        'data' => $default,
                        'required' => true
                    ]
                );
        }
        $builder
            ->add('isUnitellerWithFiscalization', CheckboxType::class, [
                'mapped' => false,
                'label' => 'form.clientPaymentSystemType.uniteller_is_with_fiscalization.label',
                'group' => 'form.clientPaymentSystemType.payment_system_group',
                'data' => $unitellerIsWithFiscalization,
                'required' => false
            ])
            ->add(
                'robokassaMerchantLogin',
                TextType::class,
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
                TextType::class,
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
                TextType::class,
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
                TextType::class,
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
                TextType::class,
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
                TextType::class,
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
                TextType::class,
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
                TextType::class,
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
                TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.uniteller_password',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params uniteller', 'type' => 'password'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $unitellerPassword
                ]
            )
            ->add(
                'taxationRateCode',
                InvertChoiceType::class,
                [
                    'label' => 'form.clientPaymentSystemType.uniteller.taxation_rate_code',
                    'choices' => $this->taxationRateCodes['rate_codes'],
                    'mapped' => false,
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params uniteller'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'data' => $taxationRateCode
                ]
            )
            ->add(
                'taxationSystemCode',
                InvertChoiceType::class,
                [
                    'label' => 'form.clientPaymentSystemType.uniteller.taxation_system_code',
                    'choices' => $this->taxationRateCodes['system_codes'],
                    'mapped' => false,
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params uniteller'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'data' => $taxationSystemCode
                ]
            )
            ->add(
                'rbkEshopId',
                TextType::class,
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
                TextType::class,
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
                TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.payment_system_paypal_login',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params paypal'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $paypalLogin
                ]
            )
            ->add(
                'successUrl',
                TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.successUrl',
                    'help' => 'form.clientPaymentSystemType.successUrlDesc',
                    'group' => 'form.clientPaymentSystemType.payment_system_group_links',
                    'required' => false,
                ]
            )
            ->add(
                'failUrl',
                TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.failUrl',
                    'help' => 'form.clientPaymentSystemType.failUrlDesc',
                    'group' => 'form.clientPaymentSystemType.payment_system_group_links',
                    'required' => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\ClientBundle\Document\ClientConfig',
            'entity' => null,
            'taxationRateCodes' => null,
            'taxationSystemCode' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_clientbundle_client_payment_system_type';
    }
}

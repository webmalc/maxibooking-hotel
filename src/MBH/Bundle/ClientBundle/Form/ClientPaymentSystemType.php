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
        /** @var ClientConfig $clientConfig */
        $clientConfig = $options['entity'];
        $robokassaMerchantLogin = $robokassaMerchantPass1 = $robokassaMerchantPass2 = null;
        $payanywayMntId = $payanywayKey = null;
        $moneymailShopIDP = $moneymailKey = null;
        $unitellerIsWithFiscalization = $unitellerShopIDP = $unitellerPassword = $taxationSystemCode = $taxationRateCode = null;
        $rbkEshopId = $rbkSecretKey = null;
        $paypalLogin = null;

        $paymentSystemName = $options['paymentSystemName'] ?? $this->paymentSystemsDefault;
        $default = $clientConfig->getPaymentSystemDocByName($paymentSystemName);

        if ($clientConfig) {
            $robokassaMerchantLogin = $clientConfig->getRobokassa() ? $clientConfig->getRobokassa()->getRobokassaMerchantLogin() : '';
            $robokassaMerchantPass1 = $clientConfig->getRobokassa() ? $clientConfig->getRobokassa()->getRobokassaMerchantPass1() : '';
            $robokassaMerchantPass2 = $clientConfig->getRobokassa() ? $clientConfig->getRobokassa()->getRobokassaMerchantPass2() : '';
            $payanywayMntId = $clientConfig->getPayanyway() ? $clientConfig->getPayanyway()->getPayanywayMntId() : '';
            $payanywayKey = $clientConfig->getPayanyway() ? $clientConfig->getPayanyway()->getPayanywayKey() : '';
            $moneymailShopIDP = $clientConfig->getMoneymail() ? $clientConfig->getMoneymail()->getMoneymailShopIDP() : '';
            $moneymailKey = $clientConfig->getMoneymail() ? $clientConfig->getMoneymail()->getMoneymailKey() : '';
            $unitellerShopIDP = $clientConfig->getUniteller() ? $clientConfig->getUniteller()->getUnitellerShopIDP() : '';
            $unitellerPassword = $clientConfig->getUniteller() ? $clientConfig->getUniteller()->getUnitellerPassword() : '';
            $unitellerIsWithFiscalization = $clientConfig->getUniteller() ? $clientConfig->getUniteller()->isWithFiscalization(): true;
            $taxationRateCode = $clientConfig->getUniteller() ? $clientConfig->getUniteller()->getTaxationRateCode() : '';
            $taxationSystemCode = $clientConfig->getUniteller() ? $clientConfig->getUniteller()->getTaxationSystemCode() : '';
            $rbkEshopId = $clientConfig->getRbk() ? $clientConfig->getRbk()->getRbkEshopId() : '';
            $rbkSecretKey = $clientConfig->getRbk() ? $clientConfig->getRbk()->getRbkSecretKey() : '';
            $paypalLogin = $clientConfig->getPaypal() ? $clientConfig->getPaypal()->getPaypalLogin() : '';
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
                    InvertChoiceType::class,
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
            'taxationSystemCode' => null,
            'paymentSystemName' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_clientbundle_client_payment_system_type';
    }
}

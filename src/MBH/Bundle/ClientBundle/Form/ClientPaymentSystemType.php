<?php

namespace MBH\Bundle\ClientBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientPaymentSystemType extends AbstractType
{
    private $paymentSystems;
    private $paymentSystemsChange;
    private $paymentSystemsDefault;
    private $taxationRateCodes;

    public function __construct($paymentSystems, $paymentSystemsChange, $paymentSystemsDefault, $taxationRateCodes)
    {
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
        $rnkbKey = $rnkbShopIDP = null;
        $rbkEshopId = $rbkSecretKey = null;
        $paypalLogin = null;
        $invoiceDocument = null;

        $paymentSystemName = $options['paymentSystemName'] ?? $this->paymentSystemsDefault;
        $paymentSystemsChoices = array_filter($this->paymentSystems, function ($paymentSystemName) use ($clientConfig, $options) {
            return !in_array($paymentSystemName, $clientConfig->getPaymentSystems()) || $paymentSystemName == $options['paymentSystemName'];
        }, ARRAY_FILTER_USE_KEY);

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
            $unitellerIsWithFiscalization = $clientConfig->getUniteller() ? $clientConfig->getUniteller()->isWithFiscalization() : true;
            $rnkbShopIDP = $clientConfig->getRnkb() ? $clientConfig->getRnkb()->getRnkbShopIDP() : '';
            $rnkbKey = $clientConfig->getRnkb() ? $clientConfig->getRnkb()->getKey() : '';
            $taxationRateCode = $clientConfig->getUniteller() ? $clientConfig->getUniteller()->getTaxationRateCode() : '';
            $taxationSystemCode = $clientConfig->getUniteller() ? $clientConfig->getUniteller()->getTaxationSystemCode() : '';
            $rbkEshopId = $clientConfig->getRbk() ? $clientConfig->getRbk()->getRbkEshopId() : '';
            $rbkSecretKey = $clientConfig->getRbk() ? $clientConfig->getRbk()->getRbkSecretKey() : '';
            $paypalLogin = $clientConfig->getPaypal() ? $clientConfig->getPaypal()->getPaypalLogin() : '';
            $rnkbShopIDP = $clientConfig->getRnkb() ? $clientConfig->getRnkb()->getRnkbShopIDP() : '';
            $rnkbKey = $clientConfig->getRnkb() ? $clientConfig->getRnkb()->getKey() : '';
            $invoiceDocument = $clientConfig->getInvoice() ? $clientConfig->getInvoice()->getInvoiceDocument() : null;
        }

        $builder
            ->add(
                'paymentSystem',
                InvertChoiceType::class,
                [
                    'label' => 'form.clientPaymentSystemType.payment_system',
                    'choices' => $paymentSystemsChoices,
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'placeholder' => '',
                    'data' => $paymentSystemName,
                    'required' => true,
                    'mapped' => false,
                    'attr' => ['disabled' => isset($options['paymentSystemName'])]
                ]
            );

        $builder
            ->add('isUnitellerWithFiscalization', CheckboxType::class, [
                'mapped' => false,
                'label' => 'form.clientPaymentSystemType.uniteller_is_with_fiscalization.label',
                'group' => 'form.clientPaymentSystemType.payment_system_group',
                'data' => $unitellerIsWithFiscalization,
                'required' => false,
                'attr' => ['class' => 'payment-system-params uniteller'],
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
                'rnkbShopIDP',
                TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.uniteller_shop_id',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params rnkb'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $rnkbShopIDP
                ]
            )
            ->add(
                'rnkbKey',
                TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.key.label',
                    'required' => false,
                    'attr' => ['class' => 'payment-system-params rnkb', 'type' => 'password'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $rnkbKey
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
            ->add('invoiceDocument', DocumentType::class, [
                'class' => DocumentTemplate::class,
                'mapped' => false,
                'data' => $invoiceDocument,
                'required' => false,
                'attr' => ['class' => 'payment-system-params invoice'],
                'group' => 'form.clientPaymentSystemType.payment_system_group',
            ])
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

<?php

namespace MBH\Bundle\ClientBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\ExtraData;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbkHelper;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\RobokassaHelper;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\TinkoffHelper;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\UnitellerHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientPaymentSystemType extends AbstractType
{
    const COMMON_ATTR_CLASS = 'payment-system-params';
    const COMMON_GROUP = 'form.clientPaymentSystemType.payment_system_group';

    /**
     * @var ExtraData
     */
    private $extraData;

    public function __construct($paymentSystems, $paymentSystemsChange, $paymentSystemsDefault, $taxationRateCodes)
    {
        $this->extraData = new ExtraData(
            $paymentSystems,
            $paymentSystemsChange,
            $paymentSystemsDefault,
            $taxationRateCodes
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ClientConfig $clientConfig */
        $clientConfig = $options['entity'];
        $payanywayMntId = $payanywayKey = null;
        $moneymailShopIDP = $moneymailKey = null;
        $rnkbKey = $rnkbShopIDP = null;
        $rbkEshopId = $rbkSecretKey = null;
        $paypalLogin = null;
        $invoiceDocument = null;
        $stripePubToken = null;

        $paymentSystemName = $options['paymentSystemName'] ?? $this->extraData->getPaymentSystemsDefault();
        $paymentSystemsChoices = array_filter($this->extraData->getPaymentSystems(), function ($paymentSystemName) use ($clientConfig, $options) {
            return !in_array($paymentSystemName, $clientConfig->getPaymentSystems()) || $paymentSystemName == $options['paymentSystemName'];
        }, ARRAY_FILTER_USE_KEY);

        if ($clientConfig) {
            $payanywayMntId = $clientConfig->getPayanyway() ? $clientConfig->getPayanyway()->getPayanywayMntId() : '';
            $payanywayKey = $clientConfig->getPayanyway() ? $clientConfig->getPayanyway()->getPayanywayKey() : '';
            $moneymailShopIDP = $clientConfig->getMoneymail() ? $clientConfig->getMoneymail()->getMoneymailShopIDP() : '';
            $moneymailKey = $clientConfig->getMoneymail() ? $clientConfig->getMoneymail()->getMoneymailKey() : '';
            $rbkEshopId = $clientConfig->getRbk() ? $clientConfig->getRbk()->getRbkEshopId() : '';
            $rbkSecretKey = $clientConfig->getRbk() ? $clientConfig->getRbk()->getRbkSecretKey() : '';
            $paypalLogin = $clientConfig->getPaypal() ? $clientConfig->getPaypal()->getPaypalLogin() : '';
            $rnkbShopIDP = $clientConfig->getRnkb() ? $clientConfig->getRnkb()->getRnkbShopIDP() : '';
            $rnkbKey = $clientConfig->getRnkb() ? $clientConfig->getRnkb()->getKey() : '';
            $invoiceDocument = $clientConfig->getInvoice() ? $clientConfig->getInvoice()->getInvoiceDocument() : null;
            $stripePubToken = $clientConfig->getStripe() ? $clientConfig->getStripe()->getPublishableToken() : null;
            $stripeSecretKey = $clientConfig->getStripe() ? $clientConfig->getStripe()->getSecretKey() : null;
            $stripeCommission = $clientConfig->getStripe() ? $clientConfig->getStripe()->getCommissionInPercents() : null;
        }

        $isPaymentSystemChanged = isset($options['paymentSystemName']);
        $builder
            ->add(
                'paymentSystem',
                InvertChoiceType::class,
                [
                    'label' => 'form.clientPaymentSystemType.payment_system',
                    'choices' => $paymentSystemsChoices,
                    'group' => self::COMMON_GROUP,
                    'placeholder' => '',
                    'data' => $paymentSystemName,
                    'required' => true,
                    'mapped' => false,
                    'attr' => ['disabled' => $isPaymentSystemChanged]
                ]
            );

        UnitellerHelper::addFields($builder, $clientConfig, $this->extraData);
        NewRbkHelper::addFields($builder, $clientConfig, $this->extraData);
        RobokassaHelper::addFields($builder, $clientConfig, $this->extraData);
        TinkoffHelper::addFields($builder, $clientConfig, $this->extraData);

        $builder
            ->add(
                'payanywayMntId',
                TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.extended_account_number',
                    'required' => false,
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' payanyway'],
                    'group' => self::COMMON_GROUP,
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
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' payanyway'],
                    'group' => self::COMMON_GROUP,
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
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' moneymail'],
                    'group' => self::COMMON_GROUP,
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
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' moneymail'],
                    'group' => self::COMMON_GROUP,
                    'mapped' => false,
                    'data' => $moneymailKey
                ]
            )
            ->add(
                'rnkbShopIDP',
                TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.uniteller_shop_id',
                    'required' => false,
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' rnkb'],
                    'group' => self::COMMON_GROUP,
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
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' rnkb', 'type' => 'password'],
                    'group' => self::COMMON_GROUP,
                    'mapped' => false,
                    'data' => $rnkbKey
                ]
            )
            ->add(
                'rbkEshopId',
                TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.rbk_eshop_id',
                    'required' => false,
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' rbk'],
                    'group' => self::COMMON_GROUP,
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
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' rbk'],
                    'group' => self::COMMON_GROUP,
                    'mapped' => false,
                    'data' => $rbkSecretKey
                ]
            )
            ->add('invoiceDocument', DocumentType::class, [
                'label' => 'form.clientPaymentSystemType.invoice_document.label',
                'class' => DocumentTemplate::class,
                'mapped' => false,
                'data' => $invoiceDocument,
                'required' => false,
                'attr' => ['class' => self::COMMON_ATTR_CLASS . ' invoice'],
                'group' => self::COMMON_GROUP,
            ])
            ->add('stripePubToken', TextType::class, [
                'label' => 'form.clientPaymentSystemType.stripe_pub_token.label',
                'mapped' => false,
                'data' => $stripePubToken,
                'required' => false,
                'attr' => ['class' => self::COMMON_ATTR_CLASS . ' stripe'],
                'group' => self::COMMON_GROUP,
            ])
            ->add('stripeSecretKey', TextType::class, [
                'label' => 'form.clientPaymentSystemType.stripe_secret_key.label',
                'mapped' => false,
                'data' => $stripeSecretKey,
                'required' => false,
                'attr' => ['class' => self::COMMON_ATTR_CLASS . ' stripe'],
                'group' => self::COMMON_GROUP,
            ])
            ->add('commission', TextType::class, [
                'label' => 'form.clientPaymentSystemType.stripe_commission.label',
                'mapped' => false,
                'data' => $stripeCommission,
                'required' => false,
                'attr' => [
                    'class' => self::COMMON_ATTR_CLASS . ' stripe mbh-spinner',
                    'spinner-max' => 100,
                    'step' => 0.05,
                    'decimals' => 2
                ],
                'group' => self::COMMON_GROUP,
            ])
            ->add(
                'paypalLogin',
                TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.payment_system_paypal_login',
                    'required' => false,
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' paypal'],
                    'group' => self::COMMON_GROUP,
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

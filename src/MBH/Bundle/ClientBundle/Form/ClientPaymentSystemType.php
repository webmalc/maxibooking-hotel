<?php

namespace MBH\Bundle\ClientBundle\Form;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\ExtraData;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbkHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
        $robokassaMerchantLogin = $robokassaMerchantPass1 = $robokassaMerchantPass2 = null;
        $payanywayMntId = $payanywayKey = null;
        $moneymailShopIDP = $moneymailKey = null;
        $unitellerShopIDP = $unitellerPassword = null;
        $rbkEshopId = $rbkSecretKey = null;
        $default = $options['default'];

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
            $rbkEshopId = $clientConfig->getRbk() ? $clientConfig->getRbk()->getRbkEshopId() : '';
            $rbkSecretKey = $clientConfig->getRbk() ? $clientConfig->getRbk()->getRbkSecretKey() : '';

            if ($clientConfig->getPaymentSystem()) {
                $default = $clientConfig->getPaymentSystem();
            }
        }

        if (!$options['change']) {
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
                    'paymentSystem',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class,
                    [
                        'label' => 'form.clientPaymentSystemType.payment_system',
                        'choices' => $options['paymentTypes'],
                        'group' => 'form.clientPaymentSystemType.payment_system_group',
                        'placeholder' => '',
                        'data' => $default,
                        'required' => true
                    ]
                );
        }

        NewRbkHelper::addFields($builder, $clientConfig, $this->extraData);

        $builder
            ->add(
                'robokassaMerchantLogin',
                TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.shop_login',
                    'required' => false,
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' robokassa'],
                    'group' => self::COMMON_GROUP,
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
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' robokassa'],
                    'group' => self::COMMON_GROUP,
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
                    'attr' => ['class' => self::COMMON_ATTR_CLASS . ' robokassa'],
                    'group' => self::COMMON_GROUP,
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
                    'attr' => ['class' => 'payment-system-params uniteller'],
                    'group' => 'form.clientPaymentSystemType.payment_system_group',
                    'mapped' => false,
                    'data' => $unitellerPassword
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
            )
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\ClientBundle\Document\ClientConfig',
            'paymentTypes' => [],
            'entity' => null,
            'default' => null,
            'change' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_clientbundle_client_payment_system_type';
    }

}

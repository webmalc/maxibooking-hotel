<?php
/**
 * Created by PhpStorm.
 * Date: 02.07.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\Robokassa;
use MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class RobokassaHelper implements HelperInterface
{
    const PREFIX = 'robokassa';

    const NAME_TYPE_TAXATION_RATE_CODE = self::PREFIX . '_TaxationRateCode';
    const NAME_TYPE_TAXATION_SYSTEM_CODE = self::PREFIX . '_TaxationSystemCode';

    public static function instance(FormInterface $form): PaymentSystemInterface
    {
        $robokassa = new Robokassa();
        $robokassa
            ->setRobokassaMerchantLogin($form->get('robokassaMerchantLogin')->getData())
            ->setRobokassaMerchantPass1($form->get('robokassaMerchantPass1')->getData())
            ->setRobokassaMerchantPass2($form->get('robokassaMerchantPass2')->getData())
            ->setIsWithFiscalization($form->get('robokassaIsWithFiscalization')->getData())
            ->setTaxationRateCode($form->get(self::NAME_TYPE_TAXATION_RATE_CODE)->getData())
            ->setTaxationSystemCode($form->get(self::NAME_TYPE_TAXATION_SYSTEM_CODE)->getData());

        return $robokassa;
    }

    public static function addFields(FormBuilderInterface $builder, ClientConfig $config, ExtraData $extraData): void
    {
        $robokassa = $config->getRobokassa() ?? new Robokassa();

        $attr = ['class' => ClientPaymentSystemType::COMMON_ATTR_CLASS . ' ' . self::PREFIX];
        $group = ClientPaymentSystemType::COMMON_GROUP;

        $builder
            ->add(
                'robokassaMerchantLogin',
                TextType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.shop_login',
                    'required' => false,
                    'attr'     => $attr,
                    'group'    => $group,
                    'mapped'   => false,
                    'data'     => $robokassa->getRobokassaMerchantLogin(),
                ]
            )
            ->add(
                'robokassaMerchantPass1',
                TextType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.password_one',
                    'required' => false,
                    'attr'     => $attr,
                    'group'    => $group,
                    'mapped'   => false,
                    'data'     => $robokassa->getRobokassaMerchantPass1(),
                ]
            )
            ->add(
                'robokassaMerchantPass2',
                TextType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.password_two',
                    'required' => false,
                    'attr'     => $attr,
                    'group'    => $group,
                    'mapped'   => false,
                    'data'     => $robokassa->getRobokassaMerchantPass2(),
                ]
            )
            ->add(
                'robokassaIsWithFiscalization',
                CheckboxType::class,
                [
                    'mapped'   => false,
                    'label'    => 'form.clientPaymentSystemType.uniteller_is_with_fiscalization.label',
                    'group'    => $group,
                    'data'     => $robokassa->isWithFiscalization(),
                    'required' => false,
                    'attr'     => $attr,
                ]
            )
            ->add(
                self::NAME_TYPE_TAXATION_RATE_CODE,
                InvertChoiceType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.uniteller.taxation_rate_code',
                    'choices'  => $extraData->getTaxationRateCodes($robokassa),
                    'mapped'   => false,
                    'required' => false,
                    'attr'     => $attr,
                    'group'    => $group,
                    'data'     => $robokassa->getTaxationRateCode(),
                ]
            )
            ->add(
                self::NAME_TYPE_TAXATION_SYSTEM_CODE,
                InvertChoiceType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.uniteller.taxation_system_code',
                    'choices'  => $extraData->getTaxationSystemCodes($robokassa),
                    'mapped'   => false,
                    'required' => false,
                    'attr'     => $attr,
                    'group'    => $group,
                    'data'     => $robokassa->getTaxationSystemCode(),
                ]
            );
        ;
    }
}
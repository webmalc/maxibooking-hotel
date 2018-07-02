<?php
/**
 * Created by PhpStorm.
 * Date: 02.07.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\Robokassa;
use MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class RobokassaHelper implements HelperInterface
{
    public static function instance(FormInterface $form): PaymentSystemInterface
    {
        $robokassa = new Robokassa();
        $robokassa->setRobokassaMerchantLogin($form->get('robokassaMerchantLogin')->getData())
            ->setRobokassaMerchantPass1($form->get('robokassaMerchantPass1')->getData())
            ->setRobokassaMerchantPass2($form->get('robokassaMerchantPass2')->getData());

        return $robokassa;
    }

    public static function addFields(FormBuilderInterface $builder, ClientConfig $config, ExtraData $extraData): void
    {
        $robokassa = $config->getRobokassa();

        $robokassaMerchantLogin = $robokassa ? $config->getRobokassa()->getRobokassaMerchantLogin() : '';
        $robokassaMerchantPass1 = $robokassa ? $config->getRobokassa()->getRobokassaMerchantPass1() : '';
        $robokassaMerchantPass2 = $robokassa ? $config->getRobokassa()->getRobokassaMerchantPass2() : '';

        $builder
            ->add(
                'robokassaMerchantLogin',
                TextType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.shop_login',
                    'required' => false,
                    'attr'     => ['class' => ClientPaymentSystemType::COMMON_ATTR_CLASS . ' robokassa'],
                    'group'    => ClientPaymentSystemType::COMMON_GROUP,
                    'mapped'   => false,
                    'data'     => $robokassaMerchantLogin,
                ]
            )
            ->add(
                'robokassaMerchantPass1',
                TextType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.password_one',
                    'required' => false,
                    'attr'     => ['class' => ClientPaymentSystemType::COMMON_ATTR_CLASS . ' robokassa'],
                    'group'    => ClientPaymentSystemType::COMMON_GROUP,
                    'mapped'   => false,
                    'data'     => $robokassaMerchantPass1,
                ]
            )
            ->add(
                'robokassaMerchantPass2',
                TextType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.password_two',
                    'required' => false,
                    'attr'     => ['class' => ClientPaymentSystemType::COMMON_ATTR_CLASS . ' robokassa'],
                    'group'    => ClientPaymentSystemType::COMMON_GROUP,
                    'mapped'   => false,
                    'data'     => $robokassaMerchantPass2,
                ]
            );
    }
}
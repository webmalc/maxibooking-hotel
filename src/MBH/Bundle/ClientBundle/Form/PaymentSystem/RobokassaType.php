<?php
/**
 * Created by PhpStorm.
 * Date: 27.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\ClientBundle\Document\PaymentSystem\Robokassa;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class RobokassaType extends PaymentSystemType
{
    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new Robokassa();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $robokassa = $builder->getData();

        $builder
            ->add(
                'robokassaMerchantLogin',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.shop_login',
                    ]
                )
            )
            ->add(
                'robokassaMerchantPass1',
                TextType::class,
                $this->addCommonAttributes([
                    'label' => 'form.clientPaymentSystemType.password_one',
                ])
            )
            ->add(
                'robokassaMerchantPass2',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.password_two',
                    ]
                )
            );

        $this->addFieldsForFiscalization($builder, $robokassa);
    }
}
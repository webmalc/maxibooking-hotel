<?php
/**
 * Created by PhpStorm.
 * Date: 24.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\ClientBundle\Document\Moneymail;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MoneyMailType extends PaymentSystemType
{
    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new Moneymail();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'moneymailShopIDP',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.moneymail_shop_id',
                    ]
                )
            )
            ->add(
                'moneymailKey',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.moneymail_key',
                    ]
                )
            );
    }
}
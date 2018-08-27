<?php
/**
 * Created by PhpStorm.
 * Date: 27.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\ClientBundle\Document\Rbk;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class RbkType extends PaymentSystemType
{
    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new Rbk();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'rbkEshopId',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.rbk_eshop_id',
                    ]
                )
            )
            ->add(
                'rbkSecretKey',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.rbk_secret_key',
                    ]
                )
            );
    }
}
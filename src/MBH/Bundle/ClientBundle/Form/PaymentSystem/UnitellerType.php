<?php
/**
 * Created by PhpStorm.
 * Date: 24.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\FormBuilderInterface;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Uniteller;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UnitellerType extends PaymentSystemType
{
    use ExtraDataTrait;
    use FiscalizationTypeTrait;

    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new Uniteller();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'unitellerShopIDP',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.uniteller_shop_id',
                    ]
                )
            )
            ->add(
                'unitellerPassword',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.uniteller_password',
                    ]
                )
            );

        $this->addFieldsForFiscalization($builder);
    }
}
<?php
/**
 * Created by PhpStorm.
 * Date: 24.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\FormBuilderInterface;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Uniteller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UnitellerType extends PaymentSystemType
{
    use ExtraDataTrait;

    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new Uniteller();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'isWithFiscalization',
                CheckboxType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.uniteller_is_with_fiscalization.label',
                    ]
                )
            )
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
            )
            ->add(
                'taxationRateCode',
                InvertChoiceType::class,
                $this->addCommonAttributes(
                    [
                        'label'   => 'form.clientPaymentSystemType.uniteller.taxation_rate_code',
                        'choices' => $this->extraData->getTaxationRateCodes(),
                    ]
                )
            )
            ->add(
                'taxationSystemCode',
                InvertChoiceType::class,
                $this->addCommonAttributes(
                    [
                        'label'   => 'form.clientPaymentSystemType.uniteller.taxation_system_code',
                        'choices' => $this->extraData->getTaxationSystemCodes(),
                    ]
                )
            );
    }
}
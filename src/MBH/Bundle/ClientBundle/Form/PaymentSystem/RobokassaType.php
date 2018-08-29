<?php
/**
 * Created by PhpStorm.
 * Date: 27.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Robokassa;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class RobokassaType extends PaymentSystemType
{
    use ExtraDataTrait;

    private const NAME_TYPE_TAXATION_RATE_CODE = 'taxationRateCode';
    private const NAME_TYPE_TAXATION_SYSTEM_CODE = 'taxationSystemCode';

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
            )
            ->add(
                'IsWithFiscalization',
                CheckboxType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.uniteller_is_with_fiscalization.label',
                    ]
                )
            )
            ->add(
                self::NAME_TYPE_TAXATION_RATE_CODE,
                InvertChoiceType::class,
                $this->addCommonAttributes(
                    [
                        'label'   => 'form.clientPaymentSystemType.uniteller.taxation_rate_code',
                        'choices' => $this->extraData->getTaxationRateCodes($robokassa),
                    ]
                )
            )
            ->add(
                self::NAME_TYPE_TAXATION_SYSTEM_CODE,
                InvertChoiceType::class,
                $this->addCommonAttributes(
                    [
                        'label'   => 'form.clientPaymentSystemType.uniteller.taxation_system_code',
                        'choices' => $this->extraData->getTaxationSystemCodes($robokassa),
                    ]
                )
            );;
    }
}
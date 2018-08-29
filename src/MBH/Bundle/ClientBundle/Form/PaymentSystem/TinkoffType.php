<?php
/**
 * Created by PhpStorm.
 * Date: 23.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Tinkoff;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TinkoffType extends PaymentSystemType
{
    use ExtraDataTrait;

    private const PREFIX_LABEL = 'form.clientPaymentSystemType.tinkoff_';

    private const NAME_TYPE_TERMINAL_KEY = 'terminalKey';
    private const NAME_TYPE_LANGUAGE = 'language';
    private const NAME_TYPE_TAXATION_RATE_CODE = 'taxationRateCode';
    private const NAME_TYPE_TAXATION_SYSTEM_CODE = 'taxationSystemCode';
    private const NAME_TYPE_IS_WITH_FISCALIZATION = 'isWithFiscalization';
    private const NAME_TYPE_SECRET_KEY = 'secretKey';
    private const NAME_TYPE_REDIRECT_DUE_DATA = 'redirectDueDate';

    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new Tinkoff();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tinkoff = $builder->getData();

        $builder
            ->add(
                self::NAME_TYPE_TERMINAL_KEY,
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => self::PREFIX_LABEL . self::NAME_TYPE_TERMINAL_KEY,
                    ]
                )

            )
            ->add(
                self::NAME_TYPE_SECRET_KEY,
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => self::PREFIX_LABEL . self::NAME_TYPE_SECRET_KEY,
                    ]
                )

            )
            ->add(
                self::NAME_TYPE_REDIRECT_DUE_DATA,
                NumberType::class,
                $this->addCommonAttributes(
                    [
                        'label' => self::PREFIX_LABEL . self::NAME_TYPE_REDIRECT_DUE_DATA,
                    ]
                )

            )
            ->add(
                self::NAME_TYPE_LANGUAGE,
                InvertChoiceType::class,
                $this->addCommonAttributes(
                    [
                        'label'   => self::PREFIX_LABEL . self::NAME_TYPE_LANGUAGE,
                        'choices' => ['ru' => 'на русском языке', 'en' => 'на английском языке'],
                    ]
                )

            )
            ->add(
                self::NAME_TYPE_TAXATION_RATE_CODE,
                InvertChoiceType::class,
                $this->addCommonAttributes(
                    [
                        'label'   => 'form.clientPaymentSystemType.uniteller.taxation_rate_code',
                        'choices' => $this->extraData->getTaxationRateCodes($tinkoff),
                    ]
                )

            )
            ->add(
                self::NAME_TYPE_TAXATION_SYSTEM_CODE,
                InvertChoiceType::class,
                $this->addCommonAttributes(
                    [
                        'label'   => 'form.clientPaymentSystemType.uniteller.taxation_system_code',
                        'choices' => $this->extraData->getTaxationSystemCodes($tinkoff),
                    ]
                )

            )
            ->add(
                self::NAME_TYPE_IS_WITH_FISCALIZATION,
                CheckboxType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.uniteller_is_with_fiscalization.label',
                    ]
                )
            );
    }
}
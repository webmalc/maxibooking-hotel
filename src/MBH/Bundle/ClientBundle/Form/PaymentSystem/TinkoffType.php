<?php
/**
 * Created by PhpStorm.
 * Date: 23.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Tinkoff;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TinkoffType extends PaymentSystemType
{
    use ExtraDataTrait;
    use FiscalizationTypeTrait;

    private const PREFIX_LABEL = 'form.clientPaymentSystemType.tinkoff_';

    private const NAME_TYPE_TERMINAL_KEY = 'terminalKey';
    private const NAME_TYPE_LANGUAGE = 'language';
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

            );

        $this->addFieldsForFiscalization($builder, $tinkoff);
    }
}
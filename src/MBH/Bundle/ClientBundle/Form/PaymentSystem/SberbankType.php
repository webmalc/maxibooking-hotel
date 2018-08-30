<?php
/**
 * Created by PhpStorm.
 * Date: 29.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\ClientBundle\Document\PaymentSystem\Sberbank;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SberbankType extends PaymentSystemType
{
    use ExtraDataTrait;
    use FiscalizationTypeTrait;

    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new Sberbank();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sberbank = $builder->getData();

        $builder
            ->add(
                'userName',
                TextType::class,
                $this->addCommonAttributes(
                    ['label' => 'form.clientPaymentSystemType.sberbank_userName']
                )
            )
            ->add(
                'password',
                TextType::class,
                $this->addCommonAttributes(
                    ['label' => 'form.clientPaymentSystemType.sberbank_password']
                )
            );

        $this->addFieldsForFiscalization($builder, $sberbank);
    }
}
<?php
/**
 * Created by PhpStorm.
 * Date: 07.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\Uniteller;
use MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class UnitellerHelper implements HelperInterface
{
    private const PREFIX = 'uniteller';

    public static function instance(FormInterface $form): PaymentSystemInterface
    {
        $uniteller = new Uniteller();
        $uniteller
            ->setUnitellerShopIDP($form->get('unitellerShopIDP')->getData())
            ->setUnitellerPassword($form->get('unitellerPassword')->getData())
            ->setIsWithFiscalization($form->get('isUnitellerWithFiscalization')->getData())
            ->setTaxationRateCode($form->get('taxationRateCode')->getData())
            ->setTaxationSystemCode($form->get('taxationSystemCode')->getData());

        return $uniteller;
    }

    public static function addFields(FormBuilderInterface $builder, ClientConfig $config, ExtraData $extraData): void
    {
        $uniteller = $config !== null ? $config->getUniteller() : null;

        if ($uniteller === null) {
            $uniteller = new Uniteller();
        }

        $commonGroup = ClientPaymentSystemType::COMMON_GROUP;
        $classCSS = ClientPaymentSystemType::COMMON_ATTR_CLASS . ' ';
        $commonAttr = ['class' => $classCSS . self::PREFIX];

        $builder
            ->add('isUnitellerWithFiscalization', CheckboxType::class, [
                'mapped'   => false,
                'label'    => 'form.clientPaymentSystemType.uniteller_is_with_fiscalization.label',
                'group'    => $commonGroup,
                'data'     => $uniteller->isWithFiscalization() ?? true,
                'required' => false,
                'attr'     => $commonAttr,
            ])
            ->add(
                'unitellerShopIDP',
                TextType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.uniteller_shop_id',
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'mapped'   => false,
                    'data'     => $uniteller->getUnitellerShopIDP(),
                ]
            )
            ->add(
                'unitellerPassword',
                TextType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.uniteller_password',
                    'required' => false,
                    'attr'     => $commonAttr + ['type' => 'password'],
                    'group'    => $commonGroup,
                    'mapped'   => false,
                    'data'     => $uniteller->getUnitellerPassword(),
                ]
            )
            ->add(
                'taxationRateCode',
                InvertChoiceType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.uniteller.taxation_rate_code',
                    'choices'  => $extraData->getTaxationRateCodes(),
                    'mapped'   => false,
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'data'     => $uniteller->getTaxationRateCode(),
                ]
            )
            ->add(
                'taxationSystemCode',
                InvertChoiceType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.uniteller.taxation_system_code',
                    'choices'  => $extraData->getTaxationSystemCodes(),
                    'mapped'   => false,
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'data'     => $uniteller->getTaxationSystemCode(),
                ]
            );
    }

}
<?php
/**
 * Created by PhpStorm.
 * Date: 01.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\Tinkoff;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TinkoffHelper implements HelperInterface
{
    private const PREFIX = 'tinkoff';
    private const PREFIX_LABEL = 'form.clientPaymentSystemType.';

    const NAME_TYPE_TERMINAL_KEY = self::PREFIX . '_terminalKey';
    const NAME_TYPE_LANGUAGE = self::PREFIX . '_language';
    const NAME_TYPE_TAXATION_RATE_CODE = self::PREFIX . '_TaxationRateCode';
    const NAME_TYPE_TAXATION_SYSTEM_CODE = self::PREFIX . '_TaxationSystemCode';
    const NAME_TYPE_IS_WITH_FISCALIZATION = self::PREFIX . '_IsWithFiscalization';

    public static function instance(FormInterface $form): PaymentSystemInterface
    {
        $entity = new Tinkoff();
        $entity->setTerminalKey($form->get(self::NAME_TYPE_TERMINAL_KEY)->getData());
        $entity->setIsWithFiscalization($form->get(self::NAME_TYPE_IS_WITH_FISCALIZATION)->getData());
        $entity->setTaxationSystemCode($form->get(self::NAME_TYPE_TAXATION_SYSTEM_CODE)->getData());
        $entity->setTaxationRateCode($form->get(self::NAME_TYPE_TAXATION_RATE_CODE)->getData());
        $entity->setLanguage($form->get(self::NAME_TYPE_LANGUAGE)->getData());

        return $entity;
    }

    public static function addFields(FormBuilderInterface $builder, ClientConfig $config, ExtraData $extraData): void
    {
        $tinkoff = $config !== null ? $config->getTinkoff() : null;

        if ($tinkoff === null) {
            $tinkoff = new Tinkoff();
        }

        $classCSS = ClientPaymentSystemType::COMMON_ATTR_CLASS . ' ';
        $commonAttr = ['class' => $classCSS . self::PREFIX];
        $commonGroup = ClientPaymentSystemType::COMMON_GROUP;

        $builder
            ->add(
                self::NAME_TYPE_TERMINAL_KEY,
                TextType::class,
                [
                    'label' => self::PREFIX_LABEL . self::NAME_TYPE_TERMINAL_KEY,
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'mapped'   => false,
                    'data' => $tinkoff->getTerminalKey()
                ]
            )
            ->add(
                self::NAME_TYPE_LANGUAGE,
                InvertChoiceType::class,
                [
                    'label'    => self::PREFIX_LABEL . self::NAME_TYPE_LANGUAGE,
                    'choices'  => ['ru' => 'на русском языке', 'en' => 'на английском языке'],
                    'mapped'   => false,
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'data'     => $tinkoff->getLanguage(),
                ]
            )
            ->add(
                self::NAME_TYPE_TAXATION_RATE_CODE,
                InvertChoiceType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.uniteller.taxation_rate_code',
                    'choices'  => $extraData->getTaxationRateCodes($tinkoff),
                    'mapped'   => false,
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'data'     => $tinkoff->getTaxationRateCode(),
                ]
            )
            ->add(
                self::NAME_TYPE_TAXATION_SYSTEM_CODE,
                InvertChoiceType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.uniteller.taxation_system_code',
                    'choices'  => $extraData->getTaxationSystemCodes($tinkoff),
                    'mapped'   => false,
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'data'     => $tinkoff->getTaxationSystemCode(),
                ]
            )
            ->add(
                self::NAME_TYPE_IS_WITH_FISCALIZATION,
                CheckboxType::class,
                [
                    'mapped'   => false,
                    'label'    => 'form.clientPaymentSystemType.uniteller_is_with_fiscalization.label',
                    'group'    => $commonGroup,
                    'data'     => $tinkoff->isWithFiscalization(),
                    'required' => false,
                    'attr'     => $commonAttr,
                ]
            );
    }
}
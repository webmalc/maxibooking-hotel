<?php
/**
 * Created by PhpStorm.
 * Date: 06.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\NewRbk;
use MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk\TaxMode;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class NewRbkHelper implements HelperInterface
{
    private const PREFIX = 'newRbk';
    private const PREFIX_LABEL = 'form.clientPaymentSystemType.';

    const NAME_TYPE_API_KEY = self::PREFIX . '_ApiKey';
    const NAME_TYPE_SHOP_ID = self::PREFIX . '_ShopId';
    const NAME_TYPE_LIFETIME_INVOICE = self::PREFIX . '_LifetimeInvoice';
    const NAME_TYPE_TAXATION_RATE_CODE = self::PREFIX . '_TaxationRateCode';
    const NAME_TYPE_WEBHOOK_KEY = self::PREFIX . '_WebhookApi';
    const NAME_TYPE_IS_WITH_FISCALIZATION = self::PREFIX . '_IsWithFiscalization';

    /**
     * @param FormInterface $form
     * @return NewRbk
     */
    public static function instance(FormInterface $form): PaymentSystemInterface
    {
        $entity = new NewRbk();
        $entity->setApiKey($form->get(self::NAME_TYPE_API_KEY)->getData());
        $entity->setShopId($form->get(self::NAME_TYPE_SHOP_ID)->getData());
        $entity->setLifetimeInvoice($form->get(self::NAME_TYPE_LIFETIME_INVOICE)->getData());
        $entity->setTaxationRateCode($form->get(self::NAME_TYPE_TAXATION_RATE_CODE)->getData());
        $entity->setWebhookKey($form->get(self::NAME_TYPE_WEBHOOK_KEY)->getData());
        $entity->setIsWithFiscalization($form->get(self::NAME_TYPE_IS_WITH_FISCALIZATION)->getData());

        return $entity;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param ClientConfig|null $config
     */
    public static function addFields(FormBuilderInterface $builder, ClientConfig $config, ExtraData $extraData): void
    {
        /** @var NewRbk $newRbk */
        $newRbk = $config !== null ? $config->getNewRbk() : null;
        if ($newRbk === null) {
            $newRbk = new NewRbk();
        }

        $classCSS = ClientPaymentSystemType::COMMON_ATTR_CLASS . ' ';
        $commonAttr = ['class' => $classCSS . self::PREFIX];
        $commonGroup = ClientPaymentSystemType::COMMON_GROUP;
        $builder
            ->add(
                self::NAME_TYPE_API_KEY,
                TextareaType::class,
                [
                    'label'    => self::PREFIX_LABEL . self::NAME_TYPE_API_KEY,
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'mapped'   => false,
                    'data'     => $newRbk->getApiKey(),
                ]
            )
            ->add(
                self::NAME_TYPE_WEBHOOK_KEY,
                TextareaType::class,
                [
                    'label'    => self::PREFIX_LABEL . self::NAME_TYPE_WEBHOOK_KEY,
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'mapped'   => false,
                    'data'     => $newRbk->getWebhookKey(),
                ]
            )
            ->add(
                self::NAME_TYPE_SHOP_ID,
                TextType::class,
                [
                    'label'    => self::PREFIX_LABEL . self::NAME_TYPE_SHOP_ID,
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'mapped'   => false,
                    'data'     => $newRbk->getShopId(),
                ]
            )
            ->add(
                self::NAME_TYPE_LIFETIME_INVOICE,
                NumberType::class,
                [
                    'label'    => self::PREFIX_LABEL . self::NAME_TYPE_LIFETIME_INVOICE,
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'mapped'   => false,
                    'data'     => $newRbk->getLifetimeInvoice(),
                ]
            )
            ->add(
                self::NAME_TYPE_IS_WITH_FISCALIZATION,
                CheckboxType::class,
                [
                    'mapped'   => false,
                    'label'    => 'form.clientPaymentSystemType.uniteller_is_with_fiscalization.label',
                    'group'    => $commonGroup,
                    'data'     => $newRbk->isWithFiscalization(),
                    'required' => false,
                    'attr'     => $commonAttr,
                ]
            )
            ->add(
                self::NAME_TYPE_TAXATION_RATE_CODE,
                InvertChoiceType::class,
                [
                    'label'    => 'form.clientPaymentSystemType.uniteller.taxation_rate_code',
                    'choices'  => $extraData->getTaxationRateCodes($newRbk),
                    'mapped'   => false,
                    'required' => false,
                    'attr'     => $commonAttr,
                    'group'    => $commonGroup,
                    'data'     => $newRbk->getTaxationRateCode(),
                ]
            );
    }
}
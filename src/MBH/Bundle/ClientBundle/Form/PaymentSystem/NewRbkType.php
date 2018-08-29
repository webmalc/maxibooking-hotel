<?php
/**
 * Created by PhpStorm.
 * Date: 27.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\NewRbk;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class NewRbkType extends PaymentSystemType
{
    use ExtraDataTrait;

    private const PREFIX = '';
    private const PREFIX_LABEL = 'form.clientPaymentSystemType.newRbk_';

    const NAME_TYPE_API_KEY = self::PREFIX . 'apiKey';
    const NAME_TYPE_SHOP_ID = self::PREFIX . 'shopId';
    const NAME_TYPE_LIFETIME_INVOICE = self::PREFIX . 'lifetimeInvoice';
    const NAME_TYPE_TAXATION_RATE_CODE = self::PREFIX . 'taxationRateCode';
    const NAME_TYPE_WEBHOOK_KEY = self::PREFIX . 'webhookKey';
    const NAME_TYPE_IS_WITH_FISCALIZATION = self::PREFIX . 'isWithFiscalization';

    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new NewRbk();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var NewRbk $newRbk */
        $newRbk = $builder->getData();

        $builder
            ->add(
                self::NAME_TYPE_API_KEY,
                TextareaType::class,
                $this->addCommonAttributes(
                    [
                        'label' => self::PREFIX_LABEL . self::NAME_TYPE_API_KEY,
                    ]
                )
            )
            ->add(
                self::NAME_TYPE_WEBHOOK_KEY,
                TextareaType::class,
                $this->addCommonAttributes(
                    [
                        'label' => self::PREFIX_LABEL . self::NAME_TYPE_WEBHOOK_KEY,
                    ]
                )
            )
            ->add(
                self::NAME_TYPE_SHOP_ID,
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => self::PREFIX_LABEL . self::NAME_TYPE_SHOP_ID,
                    ]
                )
            )
            ->add(
                self::NAME_TYPE_LIFETIME_INVOICE,
                NumberType::class,
                $this->addCommonAttributes(
                    [
                        'label' => self::PREFIX_LABEL . self::NAME_TYPE_LIFETIME_INVOICE,
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
            )
            ->add(
                self::NAME_TYPE_TAXATION_RATE_CODE,
                InvertChoiceType::class,
                $this->addCommonAttributes(
                    [
                        'label'   => 'form.clientPaymentSystemType.uniteller.taxation_rate_code',
                        'choices' => $this->extraData->getTaxationRateCodes($newRbk),
                    ]
                )
            );
    }
}
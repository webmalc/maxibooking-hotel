<?php
/**
 * Created by PhpStorm.
 * Date: 06.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\NewRbk;
use MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class NewRbkHelper implements HelperInterface
{
    private const PREFIX = 'newRbk';
    private const PREFIX_LABEL = 'form.clientPaymentSystemType.payment_system_';

    const NAME_TYPE_API_KEY = self::PREFIX . 'ApiKey';
    const NAME_TYPE_SHOP_ID = self::PREFIX . 'ShopId';
    const NAME_TYPE_LIFETIME_INVOICE = self::PREFIX . 'LifetimeInvoice';

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

        return $entity;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param ClientConfig|null $config
     */
    public static function addFields(FormBuilderInterface $builder, ClientConfig $config = null): void
    {
        /** @var NewRbk $self */
        $self = $config !== null ? $config->getNewRbk() : null;
        if ($self !== null) {
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
                        'data'     => $self->getApiKey(),
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
                        'data'     => $self->getShopId(),
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
                        'data'     => $self->getLifetimeInvoice(),
                    ]
                );
        }
    }
}
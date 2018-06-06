<?php
/**
 * Created by PhpStorm.
 * Date: 06.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

interface HelperInterface
{
    /**
     * Возращает экземпляр класса, с данными.
     * (используется в \MBH\Bundle\ClientBundle\Controller\ClientConfigController::paymentSystemSaveAction)
     *
     * @param FormInterface $form
     */
    public static function instance(FormInterface $form): PaymentSystemInterface;

    /**
     * Добавляет поля в настройки платежных систем
     * (\MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType::buildForm)
     *
     * @param FormBuilderInterface $builder
     * @param ClientConfig|null $config
     */
    public static function addFields(FormBuilderInterface $builder, ClientConfig $config = null): void ;
}
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
     * @param ClientConfig $config
     * @param array $extraData
     *
     * extraData содержит в себе данные из конструктора
     * mbh.client_payment_system_type:
     * class: MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType
     * arguments:
     *   - '%mbh.payment_systems%'
     *   - '%mbh.payment_systems.change%'
     *   - '%mbh.payment_systems.default%'
     *   - '%mbh.taxation%'
     */
    public static function addFields(FormBuilderInterface $builder, ClientConfig $config, ExtraData $extraData): void ;
}
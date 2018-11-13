<?php

namespace MBH\Bundle\ClientBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientMessagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'isSendMailAtPaymentConfirmation',
                CheckboxType::class,
                [
                    'label' => 'form.clientConfigType.is_send_mail_at_payment_confirmation.label',
                    'help' => 'form.clientConfigType.is_send_mail_at_payment_confirmation.help',
                    'group' => 'form.client_messages_type.client_mails.group',
                    'required' => false,
                ]
            )
            ->add(
                'confirmOrdersCreatedByManager',
                CheckboxType::class,
                [
                    'label' => 'form.clientConfigType.confirm_orders_created_by_manager.label',
                    'help' => 'form.clientConfigType.confirm_orders_created_by_manager.help',
                    'group' => 'form.client_messages_type.client_mails.group',
                    'required' => false,
                ]
            )
            ->add(
                'NoticeUnpaid',
                TextType::class,
                [
                    'label' => 'form.clientConfigType.notice_unpaid',
                    'group' => 'form.client_messages_type.client_mails.group',
                    'help' => 'form.clientConfigType.is_notice_unpaid',
                    'required' => true,
                ]
            )
            ->add(
                'allowNotificationTypes',
                DocumentType::class,
                [
                    'group' => 'form.client_messages_type.client_mails.group',
                    'label' => 'form.clientConfigType.notification.label',
                    'help' => 'form.clientConfigType.notification.help',
                    'required' => false,
                    'multiple' => true,
                    'class' => NotificationType::class,
                    'query_builder' => function (DocumentRepository $repository) {
                        return $repository
                            ->createQueryBuilder()
                            ->field('owner')
                            ->in(
                                [
                                    NotificationType::OWNER_CLIENT,
                                    NotificationType::OWNER_ALL,
                                ]
                            );
                    },
                    'choice_label' => function (NotificationType $type) {
                        return 'notifier.config.label.' . $type->getType();
                    },
                    'choice_attr' => function (NotificationType $type) {
                        return ['title' => 'notifier.config.title.' . $type->getType()];
                    },
                    'choice_translation_domain' => true
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'mbhclient_bundle_client_messages_type';
    }
}

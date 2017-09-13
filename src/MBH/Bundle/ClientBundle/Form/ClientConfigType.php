<?php

namespace MBH\Bundle\ClientBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClientConfigType
 */
class ClientConfigType extends AbstractType
{
    private $helper;

    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('timeZone', ChoiceType::class, [
                'choices' => ClientConfig::getTimeZonesList(),
                'group' => 'form.clientConfigType.main_group',
                'required' => false,
                'choice_label' => function ($value) {
                    return $value;
                },
                'label' => 'form.clientConfigType.time_zone.label',
                'data' => $this->helper->getTimeZone($builder->getData())
            ])
            ->add(
                'isSendSms',
                CheckboxType::class,
                [
                    'label' => 'form.clientConfigType.sms_notification',
                    'group' => 'form.clientConfigType.main_group',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.clientConfigType.is_sms_notification_turned_on',
                ]
            )
            ->add(
                'NoticeUnpaid',
                TextType::class,
                [
                    'label' => 'form.clientConfigType.notice_unpaid',
                    'group' => 'form.clientConfigType.main_group',
                    'help' => 'form.clientConfigType.is_notice_unpaid',
                    'required' => true,
                ]
            )
            ->add(
                'is_instant_search',
                CheckboxType::class,
                [
                    'label' => 'form.clientConfigType.instant_search',
                    'group' => 'form.clientConfigType.main_group',
                    'help' => 'form.clientConfigType.instant_search_help',
                    'required' => false,
                ]
            )
            ->add(
                'useRoomTypeCategory',
                CheckboxType::class,
                [
                    'label' => 'form.clientConfigType.is_disabled_room_type_category',
                    'group' => 'form.clientConfigType.main_group',
                    'required' => false,
                ]
            )
            ->add(
                'isSendMailAtPaymentConfirmation',
                CheckboxType::class,
                [
                    'label' => 'form.clientConfigType.is_send_mail_at_payment_confirmation.label',
                    'help' => 'form.clientConfigType.is_send_mail_at_payment_confirmation.help',
                    'group' => 'form.clientConfigType.main_group',
                    'required' => false,
                ]
            )
            ->add(
                'searchDates',
                TextType::class,
                [
                    'label' => 'form.clientConfigType.search_dates',
                    'help' => 'form.clientConfigType.search_dates_desc',
                    'group' => 'form.clientConfigType.search_group',
                    'required' => true,
                ]
            )
            ->add(
                'searchTariffs',
                TextType::class,
                [
                    'label' => 'form.clientConfigType.search_tariffs',
                    'help' => 'form.clientConfigType.search_tariffs_desc',
                    'group' => 'form.clientConfigType.search_group',
                    'required' => true,
                ]
            )
            ->add(
                'searchWindows',
                CheckboxType::class,
                [
                    'label' => 'form.clientConfigType.search_windows',
                    'help' => 'form.clientConfigType.search_windows_desc',
                    'group' => 'form.clientConfigType.search_group',
                    'required' => false,
                ]
            )
            ->add(
                'beginDate',
                DateType::class,
                [
                    'required' => false,
                    'label' => 'form.clientConfigType.add_start_date',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'group' => 'form.clientConfigType.main_group',
                    'attr' => [
                        'data-date-format' => 'dd.mm.yyyy',
                        'class' => 'input-small datepicker input-sm begin-datepicker',
                    ],
                ]
            )
            ->add('numberOfDaysForPayment', TextType::class, [
                'group' => 'form.clientConfigType.main_group',
                'label' => 'form.clientConfigType.number_of_days_for_payment.label',
                'help' => 'form.clientConfigType.number_of_days_for_payment.help',
                'required' => false
            ])
            ->add('currencyRatioFix', TextType::class, [
                'group' => 'form.clientConfigType.main_group',
                'required' => false,
                'label' => 'form.clientConfigType.currency_ratio_fix.label',
                'help' => 'form.clientConfigType.currency_ratio_fix.help'
            ])
            ->add(
                'can_book_without_payer',
                CheckboxType::class,
                [
                    'label' => 'form.clientConfigType.is_book_without_payer.label',
                    'help' => 'form.clientConfigType.is_book_without_payer.help',
                    'group' => 'form.clientConfigType.search_group',
                    'required' => false,
                ]
            )
            ->add(
                'defaultAdultsQuantity',
                TextType::class,
                [
                    'group' => 'form.clientConfigType.search_group',
                    'label' => 'form.clientConfigType.default_adults_quantity.label',
                    'help' => 'form.clientConfigType.default_adults_quantity.help',
                ]
            )
            ->add(
                'defaultChildrenQuantity',
                TextType::class,
                [
                    'group' => 'form.clientConfigType.search_group',
                    'label' => 'form.clientConfigType.default_children_quantity.label',
                    'help' => 'form.clientConfigType.default_children_quantity.help',
                ]
            )
            ->add(
                'allowNotificationTypes',
                DocumentType::class,
                [
                    'group' => 'form.clientConfigType.notification_group',
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
                        return 'notifier.config.label.'.$type->getType();
                    },
                    'choice_attr' => function (NotificationType $type) {
                        return ['title' => 'notifier.config.title.'.$type->getType()];
                    },
                    #http://symfony.com/blog/new-in-symfony-2-7-form-and-validator-updates#added-choice-translation-domain-domain-to-avoid-translating-options
                    'choice_translation_domain' => true
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'MBH\Bundle\ClientBundle\Document\ClientConfig',
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_clientbundle_client_config_type';
    }

}

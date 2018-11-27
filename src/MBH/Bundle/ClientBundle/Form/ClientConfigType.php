<?php

namespace MBH\Bundle\ClientBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Form\LanguageType;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClientConfigType
 */
class ClientConfigType extends AbstractType
{
    private $helper;
    private $currencyData;
    private $clientManager;

    public function __construct(Helper $helper, array $currencyData, ClientManager $clientManager)
    {
        $this->helper = $helper;
        $this->currencyData = $currencyData;
        $this->clientManager = $clientManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $client = $this->clientManager->getClient();
        $login = $client->getLogin();
        $loginAlias = !empty($client->getLoginAlias()) ? $client->getLoginAlias() : $login;

        $builder
            ->add('login_alias', TextType::class, [
                'label' => 'form.clientConfigType.time_zone.login_alias.label',
                'help' => 'form.clientConfigType.time_zone.login_alias.help',
                'mapped' => false,
                'data' => $loginAlias,
                'group' => 'form.clientConfigType.main_group',
            ])
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
            ->add('currency', ChoiceType::class, [
                'required' => false,
                'group' => 'form.clientConfigType.main_group',
                'choices' => array_keys($this->currencyData),
                'choice_label' => function ($value) {
                    return 'form.clientConfigType.currency.options.' . $value;
                },
                'label' => 'form.clientConfigType.currency.label'
            ])
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
            ->add('showLabelTips', CheckboxType::class, [
                'group' => 'form.clientConfigType.main_group',
                'required' => false,
                'label' => 'form.clientConfigType.show_label_tips.label',
                'help' => 'form.clientConfigType.show_label_tips.help'
            ])
            ->add('priceRoundSign', IntegerType::class, [
                'required' => false,
                'label' => 'form.clientConfigType.round.label',
                'help' => 'form.clientConfigType.round.help',
                'group' => 'form.clientConfigType.main_group',
                'attr' => [
                    'max' => 2,
                    'min' => 0
                ]
            ])
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
//            ->add(
//                'searchWindows',
//                CheckboxType::class,
//                [
//                    'label' => 'form.clientConfigType.search_windows',
//                    'help' => 'form.clientConfigType.search_windows_desc',
//                    'group' => 'form.clientConfigType.search_group',
//                    'required' => false,
//                ]
//            )
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
            ->add('queryStat', CheckboxType::class, [
                'label' => 'form.clientConfigType.queryStat.label',
                'help' => 'form.clientConfigType.queryStat.help',
                'group' => 'form.clientConfigType.main_group',
                'required' => false
            ])
            ->add('beginDateOffset', TextType::class, [
                'group' => 'form.clientConfigType.main_group',
                'required' => false,
                'label' => 'form.clientConfigType.begin_date_offset.label',
                'help' => 'form.clientConfigType.begin_date_offset.help'
            ])
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
            ->add('languages', LanguageType::class, [
                'label' => 'form.clientConfigType.languages.label',
                'group' => 'form.clientConfigType.main_group',
                'multiple' => true,
                'required' => false
            ])
            ->add('isMBSiteEnabled', CheckboxType::class, [
                'label' => 'form.clientConfigType.is_mb_site_enabled.label',
                'group' => 'form.clientConfigType.main_group',
                'required' => false
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

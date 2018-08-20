<?php

namespace MBH\Bundle\UserBundle\Form;

use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Lib\Model\ClientPayer;
use MBH\Bundle\BillingBundle\Lib\Model\Company;
use MBH\Bundle\BillingBundle\Lib\Model\RuCompany;
use MBH\Bundle\BillingBundle\Lib\Model\WorldCompany;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\BillingBundle\Service\BillingPayerFormHandler;
use MBH\Bundle\ClientBundle\Lib\FMSDictionaries;
use MBH\Bundle\BillingBundle\Lib\Model\Country;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use MBH\Bundle\UserBundle\Service\ClientPayerManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface;

class PayerType extends AbstractType
{
    private $fmsDictionaries;
    private $clientManager;
    private $clientPayerManager;
    private $translator;

    public function __construct(FMSDictionaries $fmsDictionaries, ClientPayerManager $clientPayerManager, ClientManager $clientManager, TranslatorInterface $translator) {
        $this->fmsDictionaries = $fmsDictionaries;
        $this->clientManager = $clientManager;
        $this->clientPayerManager = $clientPayerManager;
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Client $client */
        $client = $this->clientManager->getClient();

        /** @var ClientPayer $clientRuPayerData */
        $clientRuPayerData = $this->clientPayerManager->getClientPayer();

        /** @var Company $company */
        $company = $this->clientPayerManager->getClientCompany();
        /** @var RuCompany $ruCompany */
        $ruCompany = $this->clientPayerManager->getClientRuCompany();
        /** @var WorldCompany $worldCompany */
        $worldCompany = $this->clientPayerManager->getClientWorldCompany();
        $hasCompany = !is_null($company);
        $countryTld = isset($client) ? $client->getCountry() : Country::RUSSIA_TLD;

        $cityHelp = $this->translator->trans('form.organization_type.city.help',
            ['%plusButtonHtml%' => '<a class="add-billing-entity-button" data-entity-type="cities"><i class="fa fa-plus"></i></a>']);

        $builder
            ->add('country', TextType::class, [
                'group' => 'form.payer_type.country_group',
                'label' => 'form.payer_type.country.label',
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'countries',
                    'readonly' => true
                ],
                'data' => $countryTld
            ])
            ->add('defaultCountry', HiddenType::class, [
                'mapped' => false,
                'data' => $countryTld
            ])
            ->add('payerType', ChoiceType::class, [
                'group' => 'form.payer_type.payer_type_group',
                'choices' => [
                    'form.payer_type.legal_entity' => BillingPayerFormHandler::LEGAL_ENTITY_ID,
                    'form.payer_type.natural_entity' => BillingPayerFormHandler::NATURAL_ENTITY_ID
                ],
                'label' => 'form.payer_type.label',
                'data' => $hasCompany ? BillingPayerFormHandler::LEGAL_ENTITY_ID : BillingPayerFormHandler::NATURAL_ENTITY_ID
            ])
            ->add('address', TextType::class, [
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.address.label',
                'data' => $client->getAddress()
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'cities'
                ],
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.city.label',
                'data' => $client->getCity(),
                'help' => $cityHelp
            ])
            ->add('state', TextType::class, [
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.state.label',
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'regions'
                ],
                'data' => $client->getRegion()
            ])
            ->add('postalCode', TextType::class, [
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.postal_code.label',
                'data' => $client->getPostal_code()
            ])
            ->add('documentType', InvertChoiceType::class, [
                'choices' => $this->fmsDictionaries->getDocumentTypes(),
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.document_type.label',
                'data' => $builder->getData()['documentType'] ?? FMSDictionaries::RUSSIAN_PASSPORT_ID,
                'disabled' => true
            ])
            ->add('series', TextType::class, [
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.series.label',
                'data' => $clientRuPayerData ? $clientRuPayerData->getPassportSerial() : ''
            ])
            ->add('number', TextType::class, [
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.number.label',
                'data' => $clientRuPayerData ? $clientRuPayerData->getPassport_number() : ''
            ])
            ->add('issueDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['class' => 'datepicker begin-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'],
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.issue_date.label',
                'data' => $clientRuPayerData && $clientRuPayerData->getPassport_date()
                    ? BillingApi::getDateByBillingFormat($clientRuPayerData->getPassport_date())
                    : new \DateTime('midnight')
            ])
            ->add('registration_address', TextType::class, [
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.reg_address.label',
                'data' => $client->getAddress()
            ])
            ->add('issuedBy', TextType::class, [
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'fms'
                ],
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.issue_by.label',
                'data' => $clientRuPayerData ? $clientRuPayerData->getPassport_issued_by() : ''
            ])
            ->add('financeInn', TextType::class, [
                'group' => 'form.payer_type.financial_information.label',
                'label' => 'form.payer_type.inn.label',
                'data' => $clientRuPayerData ? $clientRuPayerData->getInn() : ''
            ])
            ->add('organizationName', TextType::class, [
                'group' => 'form.payer_type.organization.group',
                'label' => 'form.payer_type.organization.name.label',
                'data' => $hasCompany ? $company->getName() : ''
            ])
            ->add('form', ChoiceType::class, [
                'group' => 'form.payer_type.organization.group',
                'label' => 'form.payer_type.organization.form_of_legal_entity.label',
                'choices' => [
                    'ООО' => 'ooo',
                    'ОАО' => 'oao',
                    'ИП' => 'ip',
                    'ЗАО' => 'zao'
                ],
                'data' => $ruCompany ? $ruCompany->getForm() : ''
            ])
            ->add('orgState', TextType::class, [
                'label' => 'form.payer_type.state.label',
                'group' => 'form.payer_type.legal_address.group',
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'regions'
                ],
                'data' => $hasCompany ? $company->getRegion() : ''
            ])
            ->add('orgCity', TextType::class, [
                'label' => 'form.payer_type.city.label',
                'group' => 'form.payer_type.legal_address.group',
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'cities'
                ],
                'data' => $hasCompany ? $company->getCity() : '',
                'help' => $cityHelp
            ])
            ->add('orgPostalCode', TextType::class, [
                'label' => 'form.payer_type.postal_code.label',
                'group' => 'form.payer_type.legal_address.group',
                'data' => $hasCompany ? $company->getPostal_code() : ''
            ])
            ->add('orgAddress', TextType::class, [
                'label' => 'form.payer_type.organization.legal_address.label',
                'group' => 'form.payer_type.legal_address.group',
                'data' => $hasCompany ? $company->getAddress() : ''
            ])
            ->add('inn', TextType::class, [
                'label' => 'form.payer_type.organization.inn.label',
                'group' => 'form.payer_type.organization.group',
                'data' => $ruCompany ? $ruCompany->getInn() : ''
            ])
            ->add('ogrn', TextType::class, [
                'label' => 'form.payer_type.organization.ogrn.label',
                'group' => 'form.payer_type.organization.group',
                'data' => $ruCompany ? $ruCompany->getOgrn() : ''
            ])
            ->add('kpp', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.organization.group',
                'label' => 'form.payer_type.organization.kpp.label',
                'data' => $ruCompany ? $ruCompany->getKpp() : ''
            ])
//            ->add('position', TextType::class, [
//                'group' => 'form.payer_type.head.group',
//                'label' => 'form.payer_type.head.position.label'
//            ])
            ->add('surname', TextType::class, [
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.surname.label',
                'data' => $ruCompany ? $ruCompany->getBoss_lastname() : ''
            ])
            ->add('name', TextType::class, [
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.name.label',
                'data' => $ruCompany ? $ruCompany->getBoss_firstname() : ''
            ])
            ->add('patronymic', TextType::class, [
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.patronymic.label',
                'data' => $ruCompany ? $ruCompany->getBoss_patronymic() : ''
            ])
            ->add('base', ChoiceType::class, [
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.operates_on_basis_of.label',
                'choices' => [
                    'Устава' => 'charter',
                    'Доверенности' => 'proxy'
                ],
                'data' => $ruCompany ? $ruCompany->getBoss_operation_base() : ''
            ])
            ->add('proxy', TextType::class, [
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.number_of_power_of_attorney.label',
                'data' => $ruCompany ? $ruCompany->getProxy_number() : ''
            ])
            ->add('proxyDate', DateType::class, [
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.date_of_power_of_attorney.label',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['class' => 'datepicker begin-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'],
                'data' => $ruCompany && $ruCompany->getProxy_date()
                    ? BillingApi::getDateByBillingFormat($ruCompany->getProxy_date())
                    : new \DateTime('midnight')
            ])
            ->add('checkingAccount', TextType::class, [
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.checking_account.label',
                'data' => $hasCompany ? $company->getAccount_number() : ''
            ])
            ->add('bank_name', TextType::class, [
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.name.label',
                'data' => $hasCompany ? $company->getBank() : ''
            ])
            ->add('bik', TextType::class, [
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.bik.label',
                'data' => $ruCompany ? $ruCompany->getBik() : ''
            ])
            ->add('correspondentAccount', TextType::class, [
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.correspondent_account.label',
                'data' => $ruCompany ? $ruCompany->getCorr_account() : ''
            ])
            ->add('swift', TextType::class, [
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.foreign_bank.swift.label',
                'data' => $worldCompany ? $worldCompany->getSwift() : ''
            ])
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['nonDisplayGroups'] = [
            'form.payer_type.address_group',
            'form.payer_type.financial_information.label',
            'form.payer_type.identification_group',
            'form.payer_type.payer_type_group',
            'form.payer_type.organization.group',
            'form.payer_type.head.group',
            'form.payer_type.bank.group',
            'form.payer_type.foreign_organization.group',
            'form.payer_type.foreign_bank.group',
            'form.payer_type.legal_address.group'
        ];
    }

    public function getBlockPrefix()
    {
        return 'mbhuser_bundle_payer_type';
    }
}

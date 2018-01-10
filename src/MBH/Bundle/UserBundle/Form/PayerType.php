<?php

namespace MBH\Bundle\UserBundle\Form;

use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Lib\Model\Company;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\BillingBundle\Service\BillingPayerFormHandler;
use MBH\Bundle\ClientBundle\Lib\FMSDictionaries;
use MBH\Bundle\BillingBundle\Lib\Model\Country;
use MBH\Bundle\UserBundle\Service\ClientPayerManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PayerType extends AbstractType
{
    private $fmsDictionaries;

    public function __construct(FMSDictionaries $fmsDictionaries) {
        $this->fmsDictionaries = $fmsDictionaries;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Client $client */
        $client = $options['client'];
        $clientRuPayerData = !is_null($client->getRu()) ? $client->getRu() : null;

        /** @var Company $company */
        $company = $options['company'];
        $hasCompany = !is_null($company);

        $builder
            ->add('country', TextType::class, [
                'group' => 'form.payer_type.country_group',
                'label' => 'form.payer_type.country.label',
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'countries'
                ],
                'data' => isset($client) ? $client->getCountry() : Country::RUSSIA_TLD
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
                'required' => false,
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.address.label',
                'data' => $client->getAddress()
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'cities'
                ],
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.city.label',
                'data' => $client->getCity()
            ])
            ->add('state', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.state.label',
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'regions'
                ],
                'data' => $client->getRegion()
            ])
            ->add('postalCode', IntegerType::class, [
                'required' => false,
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.postal_code.label',
                'data' => $client->getPostal_code()
            ])
            ->add('documentType', InvertChoiceType::class, [
                'required' => false,
                'choices' => $this->fmsDictionaries->getDocumentTypes(),
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.document_type.label',
                'data' => $builder->getData()['documentType'] ?? FMSDictionaries::RUSSIAN_PASSPORT_ID,
                'disabled' => true
            ])
            ->add('series', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.series.label',
                'data' => $clientRuPayerData ? $clientRuPayerData['passport_serial'] : ''
            ])
            ->add('number', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.number.label',
                'data' => $clientRuPayerData ? $clientRuPayerData['passport_number'] : ''
            ])
            ->add('issueDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['class' => 'datepicker begin-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'],
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.issue_date.label',
                'data' => $clientRuPayerData ? \DateTime::createFromFormat(BillingApi::BILLING_DATETIME_FORMAT, $clientRuPayerData['passport_date']) : new \DateTime('midnight')
            ])
            ->add('issuedBy', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'fms'
                ],
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.issue_by.label',
                'data' => $clientRuPayerData ? $clientRuPayerData['passport_issued_by'] : ''
            ])
            ->add('financeInn', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.financial_information.label',
                'label' => 'form.payer_type.inn.label',
                'data' => $clientRuPayerData ? $clientRuPayerData['inn'] : ''
            ])
            ->add('organizationName', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.organization.group',
                'label' => 'form.payer_type.organization.name.label',
                'data' => $hasCompany ? $company->getName() : ''
            ])
            ->add('form', ChoiceType::class, [
                'required' => false,
                'group' => 'form.payer_type.organization.group',
                'label' => 'form.payer_type.organization.form_of_legal_entity.label',
                'choices' => [
                    'ООО' => 'ooo',
                    'ОАО' => 'oao',
                    'ИП' => 'ip',
                    'ЗАО' => 'zao'
                ],
                'data' => $hasCompany ? $company->getRuPayerCompanyData('form') : ''
            ])
            ->add('orgAddress', TextType::class, [
                'required' => false,
                'label' => 'form.payer_type.organization.legal_address.label',
                'group' => 'form.payer_type.organization.group',
                'data' => $hasCompany ? $company->getAddress() : ''
            ])
            ->add('orgState', TextType::class, [
                'required' => false,
                'label' => 'form.payer_type.state.label',
                'group' => 'form.payer_type.organization.group',
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'regions'
                ],
                'data' => $hasCompany ? $company->getRegion() : ''
            ])
            ->add('orgCity', TextType::class, [
                'required' => false,
                'label' => 'form.payer_type.city.label',
                'group' => 'form.payer_type.organization.group',
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'cities'
                ],
                'data' => $hasCompany ? $company->getCity() : ''
            ])
            ->add('orgPostalCode', TextType::class, [
                'required' => false,
                'label' => 'form.payer_type.postal_code.label',
                'group' => 'form.payer_type.organization.group',
                'data' => $hasCompany ? $company->getPostal_code() : ''
            ])
            ->add('inn', TextType::class, [
                'required' => false,
                'label' => 'form.payer_type.organization.inn.label',
                'group' => 'form.payer_type.organization.group',
                'data' => $hasCompany ? $company->getRuPayerCompanyData('inn') : ''
            ])
            ->add('ogrn', TextType::class, [
                'required' => false,
                'label' => 'form.payer_type.organization.ogrn.label',
                'group' => 'form.payer_type.organization.group',
                'data' => $hasCompany ? $company->getRuPayerCompanyData('ogrn') : ''
            ])
            ->add('kpp', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.organization.group',
                'label' => 'form.payer_type.organization.kpp.label',
                'data' => $hasCompany ? $company->getRuPayerCompanyData('kpp') : ''
            ])
//            ->add('position', TextType::class, [
//                'required' => false,
//                'group' => 'form.payer_type.head.group',
//                'label' => 'form.payer_type.head.position.label'
//            ])
            ->add('surname', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.surname.label',
                'data' => $hasCompany ? $company->getRuPayerCompanyData('boss_lastname') : ''
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.name.label',
                'data' => $hasCompany ? $company->getRuPayerCompanyData('boss_firstname') : ''
            ])
            ->add('patronymic', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.patronymic.label',
                'data' => $hasCompany ? $company->getRuPayerCompanyData('boss_patronymic') : ''
            ])
            ->add('base', ChoiceType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.operates_on_basis_of.label',
                'choices' => [
                    'Устава' => 'charter',
                    'Доверенности' => 'proxy'
                ],
                'data' => $hasCompany ? $company->getRuPayerCompanyData('boss_operation_base') : ''
            ])
            ->add('proxy', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.number_of_power_of_attorney.label',
                'data' => $hasCompany ? $company->getRuPayerCompanyData('proxy_number') : ''
            ])
            ->add('proxyDate', DateType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.date_of_power_of_attorney.label',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['class' => 'datepicker begin-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'],
                'data' => $hasCompany && $company->getRu() ? \DateTime::createFromFormat(BillingApi::BILLING_DATETIME_FORMAT, $company->getRuPayerCompanyData('proxy_date')) : new \DateTime('midnight')
            ])
            ->add('checkingAccount', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.checking_account.label',
                'data' => $hasCompany ? $company->getAccount_number() : ''
            ])
            ->add('bank_name', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.name.label',
                'data' => $hasCompany ? $company->getBank() : ''
            ])
            ->add('bik', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.bik.label',
                'data' => $hasCompany ? $company->getRuPayerCompanyData('bik') : ''
            ])
            ->add('correspondentAccount', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.correspondent_account.label',
                'data' => $hasCompany ? $company->getRuPayerCompanyData('corr_account') : ''
            ])
            ->add('swift', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.foreign_bank.swift.label',
                'data' => $hasCompany && !is_null($company->getWorld()) ? $company->getWorld()['swift'] : ''
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'client' => null,
            'company' => null
        ]);
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
            'form.payer_type.foreign_bank.group'
        ];
    }

    public function getBlockPrefix()
    {
        return 'mbhuser_bundle_payer_type';
    }
}

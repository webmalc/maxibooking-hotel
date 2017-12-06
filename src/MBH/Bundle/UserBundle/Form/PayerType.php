<?php

namespace MBH\Bundle\UserBundle\Form;

use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\ClientBundle\Lib\FMSDictionaries;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use MBH\Bundle\PackageBundle\Models\Billing\Country;
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
                    'form.payer_type.legal_entity' => ClientPayerManager::LEGAL_ENTITY_ID,
                    'form.payer_type.natural_entity' => ClientPayerManager::NATURAL_ENTITY_ID
                ],
                'label' => 'form.payer_type.label'
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
                'data' => $clientRuPayerData ? \DateTime::createFromFormat(BillingApi::BILLING_DATETIME_FORMAT, $clientRuPayerData['passport_date']) : ''
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
                'label' => 'form.payer_type.organization.name.label'
            ])
            ->add('form', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.organization.group',
                'label' => 'form.payer_type.organization.form_of_legal_entity.label'
            ])
            ->add('legalAddress', TextType::class, [
                'required' => false,
                'label' => 'form.payer_type.organization.legal_address.label',
                'group' => 'form.payer_type.organization.group',
            ])
            ->add('inn', TextType::class, [
                'required' => false,
                'label' => 'form.payer_type.organization.inn.label',
                'group' => 'form.payer_type.organization.group',
            ])
            ->add('ogrn', TextType::class, [
                'required' => false,
                'label' => 'form.payer_type.organization.ogrn.label',
                'group' => 'form.payer_type.organization.group',
            ])
            ->add('position', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.position.label'
            ])
            ->add('surname', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.surname.label'
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.name.label'
            ])
            ->add('patronymic', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.patronymic.label'
            ])
            ->add('base', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.operates_on_basis_of.label'
            ])
            ->add('proxy', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.number_of_power_of_attorney.label'
            ])
            ->add('proxyDate', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.head.group',
                'label' => 'form.payer_type.head.date_of_power_of_attorney.label'
            ])
            ->add('checkingAccount', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.checking_account.label'
            ])
            ->add('bank_name', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.name.label'
            ])
            ->add('bik', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.bik.label'
            ])
            ->add('correspondentAccount', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.bank.group',
                'label' => 'form.payer_type.bank.correspondent_account.label'
            ])
            ->add('foreignOrgName', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.foreign_organization.group',
                'label' => 'form.payer_type.foreign_organization.postal_code.label'
            ])
            ->add('foreignOrgAddress', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.foreign_organization.group',
                'label' => 'form.payer_type.foreign_organization.address.label'
            ])
            ->add('foreignOrgCity', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.foreign_organization.group',
                'label' => 'form.payer_type.foreign_organization.city.label'
            ])
            ->add('foreignOrgState', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.foreign_organization.group',
                'label' => 'form.payer_type.foreign_organization.state.label'
            ])
            ->add('foreignOrgPostal', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.foreign_organization.group',
                'label' => 'form.payer_type.foreign_organization.postal_code.label'
            ])
            ->add('foreignBankIban', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.foreign_bank.group',
                'label' => 'form.payer_type.foreign_bank.iban.label'
            ])
            ->add('foreignBankName', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.foreign_bank.group',
                'label' => 'form.payer_type.foreign_bank.name.label'
            ])
            ->add('foreignBankSwift', TextType::class, [
                'required' => false,
                'group' => 'form.payer_type.foreign_bank.group',
                'label' => 'form.payer_type.foreign_bank.swift.label'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'client' => null
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

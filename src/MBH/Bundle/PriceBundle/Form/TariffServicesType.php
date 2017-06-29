<?php

namespace MBH\Bundle\PriceBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TariffServicesType

 */
class TariffServicesType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('services', DocumentType::class, [
                'label' => 'mbhpricebundle.form.tariffpromotionstype.dostupnyyeaktsii',
                'group' => 'mbhpricebundle.form.tariffservicetype.common_group_name',
                'required' => false,
                'attr' => ['data-placeholder' => $this->translator->trans('mbhpricebundle.form.tariffservicestype.vseuslugi')],
                'class' => 'MBH\Bundle\PriceBundle\Document\Service',
                'choices' => $options['services_all'],
                'multiple' => true
            ])
            ->add('defaultServices', CollectionType::class, [
                'label' => 'mbhpricebundle.form.tariffservicestype.uslugipoumolchaniyu',
                'group' => 'mbhpricebundle.form.tariffservicetype.common_group_name',
                'required' => false,
                'entry_type' => TariffServiceType::class,
                'entry_options' => ['services' => $options['services']],
                'allow_add' => true,
                'allow_delete' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Tariff',
            'services' => [],
            'services_all' => []
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_price_tariff_promotions';
    }

}
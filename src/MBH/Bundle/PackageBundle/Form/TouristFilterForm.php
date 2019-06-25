<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Criteria\TouristQueryCriteria;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\DataCollectorTranslator;

class TouristFilterForm extends AbstractType
{
    /** @var DataCollectorTranslator */
    protected $trans;

    public function __construct(DataCollectorTranslator $trans)
    {
        $this->trans = $trans;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'begin',
                DateType::class,
                [
                    'widget'   => 'single_text',
                    'format'   => 'dd.MM.yyyy',
                    'required' => false,
                ]
            )
            ->add(
                'end',
                DateType::class,
                [
                    'widget'   => 'single_text',
                    'format'   => 'dd.MM.yyyy',
                    'required' => false,
                ]
            )
            ->add(
                'hotels',
                DocumentType::class,
                [
                    'class'         => Hotel::class,
                    'query_builder' => function (DocumentRepository $er) {
                        $qb = $er->createQueryBuilder();
                        $qb->field('isEnabled')->equals(true);

                        return $qb;
                    },
                    'placeholder'   => '',
                    'required'      => false,
                    'multiple'      => true,
                ]
            )
            ->add(
                'citizenship',
                InvertChoiceType::class,
                [
                    'required' => false,
                    'choices'  => [
                        TouristQueryCriteria::CITIZENSHIP_NATIVE  => $this->trans->trans('tourist.filter.citizen.ru.type'),
                        TouristQueryCriteria::CITIZENSHIP_FOREIGN => $this->trans->trans('tourist.filter.citizen.foreign.type'),
                    ],
                ]
            )
            ->add(
                'search',
                TextType::class,
                [
                    'required' => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TouristQueryCriteria::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhpackage_bundle_tourist_filter_form';
    }
}

<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HousingType
 */
class HousingType extends AbstractType
{
    /**
     * @var DocumentManager
     */
    private $dm;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->dm = $options['dm'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'views.corpus.name',
                'group' => 'views.form.corpus.group.main',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('internalName', TextType::class, [
                'required' => false,
                'group' => 'views.form.corpus.group.main',
                'label' => 'views.corpus.internal_name',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('cityId', TextType::class, [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.city',
                'translation_domain' => 'MBHHotelBundle',
                'attr' => ['class' => 'citySelect']
            ])
            ->add('settlement', TextType::class, [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.settlement',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('street', TextType::class, [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.street',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('house', TextType::class, [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.house',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('corpus', TextType::class, [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.corpus',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('flat', TextType::class, [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.flat',
                'translation_domain' => 'MBHHotelBundle'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Housing',
            'dm' => null
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mbh_corpus';
    }
}
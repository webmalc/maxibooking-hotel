<?php

namespace MBH\Bundle\HotelBundle\Form;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HousingType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class HousingType extends AbstractType
{
    /**
     * @var DocumentManager
     */
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'label' => 'views.corpus.name',
                'group' => 'views.form.corpus.group.main',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('internalName', 'text', [
                'required' => false,
                'group' => 'views.form.corpus.group.main',
                'label' => 'views.corpus.internal_name',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('city', 'text', [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.city',
                'translation_domain' => 'MBHHotelBundle',
                'attr' => ['class' => 'citySelect']
            ])
            ->add('settlement', 'text', [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.settlement',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('street', 'text', [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.street',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('house', 'text', [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.house',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('corpus', 'text', [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.corpus',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('flat', 'text', [
                'required' => false,
                'group' => 'views.form.corpus.group.address',
                'label' => 'views.corpus.flat',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('vega_address_id', 'number', [
                'label' => 'form.hotelExtendedType.vega_address_id',
                'help' => 'form.hotelExtendedType.vega_address_id_help',
                'group' => 'views.form.corpus.group.config',
                'required' => false
        ]);

        $builder->get('city')->addViewTransformer(new EntityToIdTransformer($this->dm, 'MBHHotelBundle:City'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Housing'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mbh_corpus';
    }
}
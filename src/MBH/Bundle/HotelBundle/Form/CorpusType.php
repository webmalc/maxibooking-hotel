<?php

namespace MBH\Bundle\HotelBundle\Form;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CorpusType
 * @package MBH\Bundle\HotelBundle\Form
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class CorpusType extends AbstractType
{
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
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('internalName', 'text', [
                'required' => false,
                'label' => 'views.corpus.internal_name',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('city', 'text', [
                'required' => false,
                'label' => 'views.corpus.city',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('street', 'text', [
                'required' => false,
                'label' => 'views.corpus.street',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('house', 'text', [
                'required' => false,
                'label' => 'views.corpus.house',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('flat', 'text', [
                'required' => false,
                'label' => 'views.corpus.flat',
                'translation_domain' => 'MBHHotelBundle'
            ])
            ->add('corpus', 'text', [
                'required' => false,
                'label' => 'views.corpus.corpus',
                'translation_domain' => 'MBHHotelBundle'
            ]);

        $builder->get('city')->addViewTransformer(new EntityToIdTransformer($this->dm, 'MBHHotelBundle:City'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Corpus'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'mbh_corpus';
    }
}
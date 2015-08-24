<?php

namespace MBH\Bundle\BaseBundle\Form;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FacilitiesType
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class FacilitiesType extends AbstractType
{
    use ContainerAwareTrait;

    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
            $event->setData([]);
        });
    }


    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $facilityRepository = $this->container->get('mbh.facility_repository');
        $view->vars['data'] = $facilityRepository->sortByConfig($view->vars['data']);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->container->get('mbh.facility_repository')->getAllByGroup(),
            'multiple' => true,
            'choice_attr' => function($key, $label) {
                return [
                    'data-icon' => 'mbf-'.$key
                ];
            },
            'translation_domain' => 'messages',
            'attr' => [
                'class' => 'plain-html',
                'placeholder' => 'Выберите теги'
            ],
            'empty_value' => '',

            'label' => 'form.hotelExtendedType.hotel_amenities'
        ]);
    }


    public function getName()
    {
        return 'mbh_facilities';
    }
}
<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;

class SearchType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dm = $options['dm'];
        if (!$dm) {
            throw new \Exception('Unable find Document Manager');
        }

        $hotels = $dm->getRepository('MBHHotelBundle:Hotel')->createQueryBuilder('h')
                     ->sort('fullTitle', 'asc')
                     ->getQuery()
                     ->execute()
        ;
        $roomTypes = [];
        
        foreach ($hotels as $hotel) {

            if (!$options['security']->checkPermissions($hotel)) {
                continue;
            }

            $roomTypes[$hotel->getName()]['allrooms_' . $hotel->getId()] = 'form.searchType.all_rooms';
            foreach($hotel->getRoomTypes() as $roomType) {
                $roomTypes[$hotel->getName()][$roomType->getId()] = $roomType->getName();
            }
        }
        $data = [];
        if ($options['hotel']) {
            $data = ['allrooms_' . $options['hotel']->getId()];
        }

        $builder
                ->add('roomType', 'choice', [
                    'label' => 'form.searchType.room_type',
                    'required' => false,
                    'multiple' => true,
                    'error_bubbling' => true,
                    'choices' => $roomTypes,
                    'data' => $data
                ])
                ->add('tariff', 'document', [
                    'label' => 'form.searchType.tariff',
                    'required' => false,
                    'multiple' => false,
                    'error_bubbling' => true,
                    'class' => 'MBHPriceBundle:Tariff',
                    'attr'  => ['class' => 'plain-html']
                ])
                ->add('begin', 'date', array(
                    'label' => 'form.searchType.check_in',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'data' => new \DateTime(),
                    'required' => true,
                    'error_bubbling' => true,
                    'attr' => array('class' => 'datepicker begin-datepiker', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(['message' => 'form.searchType.check_in_date_not_filled']), new Date()]
                ))
                ->add('end', 'date', array(
                    'label' => 'Отъезд',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'data' => new \DateTime('+1 day'),
                    'required' => true,
                    'error_bubbling' => true,
                    'attr' => array('class' => 'datepicker end-datepiker', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(['message' => 'form.searchType.check_out_date_not_filled']), new Date()]
                ))
                ->add('adults', 'text', [
                    'label' => 'form.searchType.adults',
                    'required' => true,
                    'error_bubbling' => true,
                    'data' => 0,
                    'attr' => ['class' => 'spinner'],
                    'constraints' => [
                        new Range(['min' => 0, 'minMessage' => 'form.searchType.adults_amount_less_zero']),
                        new NotBlank(['message' => 'form.searchType.adults_amount_not_filled'])
                    ]
                ])
                ->add('children', 'text', [
                    'label' => 'form.searchType.children',
                    'required' => true,
                    'error_bubbling' => true,
                    'data' => 0,
                    'attr' => ['class' => 'spinner'],
                    'constraints' => [
                        new Range(['min' => 0, 'minMessage' => 'form.searchType.children_amount_less_zero']),
                        new NotBlank(['message' => 'form.searchType.children_amount_not_filled'])
                    ]
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'dm' => null,
            'security' => null,
            'hotel' => null,
        ]);
    }

    public function getName()
    {
        return 's';
    }

}

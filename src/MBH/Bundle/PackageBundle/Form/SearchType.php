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

            $roomTypes[$hotel->getName()]['allrooms_' . $hotel->getId()] = 'Все номера';
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
                    'label' => 'Тип номера',
                    'required' => false,
                    'multiple' => true,
                    'error_bubbling' => true,
                    'choices' => $roomTypes,
                    'data' => $data
                ])
                ->add('tariff', 'document', [
                    'label' => 'Тафиф',
                    'required' => false,
                    'multiple' => false,
                    'error_bubbling' => true,
                    'class' => 'MBHPriceBundle:Tariff',
                    'attr'  => ['class' => 'plain-html']
                ])
                ->add('begin', 'date', array(
                    'label' => 'Заезд',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'data' => new \DateTime(),
                    'required' => true,
                    'error_bubbling' => true,
                    'attr' => array('class' => 'datepicker begin-datepiker', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(['message' => 'Не заполнена дата заезда']), new Date()]
                ))
                ->add('end', 'date', array(
                    'label' => 'Отъезд',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'data' => new \DateTime('+1 day'),
                    'required' => true,
                    'error_bubbling' => true,
                    'attr' => array('class' => 'datepicker end-datepiker', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(['message' => 'Не заполнена дата отъезда']), new Date()]
                ))
                ->add('adults', 'text', [
                    'label' => 'Взрослые',
                    'required' => true,
                    'error_bubbling' => true,
                    'data' => 0,
                    'attr' => ['class' => 'spinner'],
                    'constraints' => [
                        new Range(['min' => 0, 'minMessage' => 'Количество взрослых не может быть меньше нуля']),
                        new NotBlank(['message' => 'Не заполнено количество взрослых'])
                    ]
                ])
                ->add('children', 'text', [
                    'label' => 'Дети',
                    'required' => true,
                    'error_bubbling' => true,
                    'data' => 0,
                    'attr' => ['class' => 'spinner'],
                    'constraints' => [
                        new Range(['min' => 0, 'minMessage' => 'Количество детей не может быть меньше нуля']),
                        new NotBlank(['message' => 'Не заполнено количество детей'])
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

<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SearchType
 */
class SearchType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dm = $options['dm'];
        if (!$dm) {
            throw new Exception('Unable to find Document Manager');
        }

        $hotels = $dm->getRepository('MBHHotelBundle:Hotel')->createQueryBuilder('h')
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();
		
        $roomTypes = [];

        foreach ($hotels as $hotel) {

            if (!$options['security']->checkPermissions($hotel)) {
                continue;
            }

            $roomTypes[$hotel->getName()]['allrooms_' . $hotel->getId()] = 'form.searchType.all_rooms';
			
            foreach ($options['roomManager']->getRooms($hotel) as $roomType) {
                $roomTypes[$hotel->getName()][$roomType->getId()] = $roomType->getName();
            }
        }

        $builder
            ->add('tourist', 'text', [
                'label' => 'form.searchType.fio',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'form.orderTouristType.placeholder_fio',
                    'style' => 'min-width: 350px !important; width: 350px !important;',
                    'class' => 'findGuest'
                ]
            ])
            ->add('order', 'integer', [
                'label' => 'form.searchType.order',
                'required' => false,
                'mapped' => false,
                'error_bubbling' => true,
                'data' => $options['orderId'],
                'attr' => ['class' => 'input-xs only-int'],
            ])
            ->add('roomType', 'choice', [
                'label' => 'form.searchType.room_type',
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'error_bubbling' => true,
                'choices' => $roomTypes,
            ])
            ->add('tariff', 'document', [
                'label' => 'form.searchType.tariff',
                'required' => false,
                'multiple' => false,
                'error_bubbling' => true,
                'class' => 'MBHPriceBundle:Tariff',
                'attr' => ['class' => 'plain-html']
            ])
            ->add('begin', 'date', array(
                'label' => 'form.searchType.check_in',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'data' => $options['startDate'],
                'required' => true,
                'error_bubbling' => true,
                'attr' => array('class' => 'datepicker begin-datepicker mbh-daterangepicker', 'data-date-format' => 'dd.mm.yyyy')
            ))
            ->add('end', 'date', array(
                'label' => 'Отъезд',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'data' => (clone $options['startDate'])->modify("+1 day"),
                'required' => true,
                'error_bubbling' => true,
                'attr' => array('class' => 'datepicker end-datepicker mbh-daterangepicker', 'data-date-format' => 'dd.mm.yyyy')
            ))
            ->add('range', 'integer', array(
                'label' => 'form.searchType.range',
                'required' => false,
                'mapped' => true,
                'error_bubbling' => true,
                'attr' => ['class' => 'input-xxs only-int not-null', 'min' => 0, 'max' => 10],
            ))
            ->add('adults', 'integer', [
                'label' => 'form.searchType.adults',
                'required' => true,
                'error_bubbling' => true,
                'data' => 1,
                'attr' => ['class' => 'input-xxs only-int not-null', 'min' => 0, 'max' => 10],
            ])
            ->add('children', 'integer', [
                'label' => 'form.searchType.children',
                'required' => true,
                'error_bubbling' => true,
                'data' => 0,
                'attr' => ['class' => 'input-xxs only-int not-null', 'min' => 0, 'max' => 6],
            ])
            ->add('forceBooking', 'checkbox', [
                'label' => 'form.searchType.forceBooking',
                'required' => false,
            ])
            ->add('room', 'hidden', [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'dm' => null,
            'security' => null,
            'hotel' => null,
            'orderId' => null,
            'roomManager' => null,
            'startDate' => new \DateTime(),
            'data_class' => 'MBH\Bundle\PackageBundle\Lib\SearchQuery'
        ]);
    }

    public function getName()
    {
        return 's';
    }

}

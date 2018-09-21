<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SearchType
 */
class SearchType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var DocumentManager $dm */
        $dm = $options['dm'];
        if (!$dm) {
            throw new Exception('Unable to find Document Manager');
        }

        $hotels = $dm->getRepository('MBHHotelBundle:Hotel')->createQueryBuilder()
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();
		
        $roomTypes = [];

        /** @var Hotel $hotel */
        foreach ($hotels as $hotel) {

            if (!$options['security']->checkPermissions($hotel)) {
                continue;
            }

            $roomTypes[$hotel->getName()]['allrooms_' . $hotel->getId()] = 'form.searchType.all_rooms';

            /** @var RoomType $roomType */
            foreach ($options['roomManager']->getRooms($hotel) as $roomType) {
                $roomTypes[$hotel->getName()][$roomType->getId()] = $roomType->getName();
            }
        }
        /** @var ClientConfig $clientConfig */
        $clientConfig = $dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();

        $builder
            ->add('tourist', TextType::class, [
                'label' => 'form.searchType.fio',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'form.orderTouristType.placeholder_fio',
                    'style' => 'min-width: 350px !important; width: 350px !important;',
                    'class' => 'findGuest'
                ]
            ])
            ->add('order', IntegerType::class, [
                'label' => 'form.searchType.order',
                'required' => false,
                'mapped' => false,
                'error_bubbling' => true,
                'data' => $options['orderId'],
                'attr' => ['class' => 'input-xs only-int'],
            ])
            ->add('roomType',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'form.searchType.room_type',
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'error_bubbling' => true,
                'choices' => $roomTypes,
            ])
            ->add('tariff', DocumentType::class, [
                'label' => 'form.searchType.tariff',
                'required' => false,
                'multiple' => false,
                'error_bubbling' => true,
                'class' => 'MBHPriceBundle:Tariff',
                'attr' => ['class' => 'plain-html']
            ])
            ->add('begin', DateType::class, array(
                'label' => 'form.searchType.check_in',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'error_bubbling' => true,
                'attr' => array('class' => 'datepicker begin-datepicker mbh-daterangepicker', 'data-date-format' => 'dd.mm.yyyy')
            ))
            ->add('end', DateType::class, array(
                'label' => 'form.searchType.check_out',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'error_bubbling' => true,
                'attr' => array('class' => 'datepicker end-datepicker mbh-daterangepicker', 'data-date-format' => 'dd.mm.yyyy')
            ))
            ->add('range', IntegerType::class, array(
                'label' => 'form.searchType.range',
                'required' => false,
                'mapped' => true,
                'error_bubbling' => true,
                'attr' => ['class' => 'input-xxs only-int not-null', 'min' => 0, 'max' => 10],
            ))
            ->add('adults', IntegerType::class, [
                'label' => 'form.searchType.adults',
                'required' => true,
                'error_bubbling' => true,
                'data' => $clientConfig->getDefaultAdultsQuantity(),
                'attr' => ['class' => 'input-xxs only-int not-null', 'min' => 0, 'max' => 12],
            ])
            ->add('children', IntegerType::class, [
                'label' => 'form.searchType.children',
                'required' => true,
                'error_bubbling' => true,
                'data' => $clientConfig->getDefaultChildrenQuantity(),
                'attr' => ['class' => 'input-xxs only-int not-null', 'min' => 0, 'max' => 6],
            ])
            ->add('special', DocumentType::class, [
                'label' => 'form.searchType.special',
                'required' => false,
                'multiple' => false,
                'error_bubbling' => true,
                'class' => 'MBHPriceBundle:Special',
                'attr' => ['class' => 'plain-html']
            ])
            ->add('forceBooking', CheckboxType::class, [
                'label' => 'form.searchType.forceBooking',
                'required' => false,
            ])
            ->add(
                'disabledIsOpen',
                CheckboxType::class,
                [
                    'label' => 'form.searchType.disabledIsOpen',
                    'required' => false,
                ]
            )
            ->add('room', HiddenType::class, [
                'required' => false
            ])
            ->add('limit', HiddenType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'allow_extra_fields' => true,
            'dm' => null,
            'security' => null,
            'hotel' => null,
            'orderId' => null,
            'roomManager' => null,
            'startDate' => new \DateTime(),
            'data_class' => SearchQuery::class,
            'method' => 'GET',
            'client_config' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 's';
    }

}

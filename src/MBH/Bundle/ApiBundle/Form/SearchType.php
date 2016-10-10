<?php

namespace MBH\Bundle\ApiBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\BaseBundle\Form\Extension\DateType;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use Symfony\Component\Form\AbstractType;
use MBH\Bundle\ApiBundle\Lib\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class SearchType
 */
class SearchType extends AbstractType
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var RoomTypeManager
     */
    protected $manager;

    public function __construct(Helper $helper, RoomTypeManager $manager)
    {
        $this->helper = $helper;
        $this->manager = $manager;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if (!$options['config']) {
            throw new Exception('FormConfig not found ($options[config] == null)');
        }

        $config = $options['config'];
        $hotels = $config->getHotels();
        $builder
            ->add('begin', DateType::class, array(
                'label' => 'api.search.form.begin',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'attr' => ['class' => 'datepicker', 'data-date-format' => 'dd.mm.yyyy']
            ));

        if ($config->getNights()) {
            $builder
                ->add('nights', ChoiceType::class, [
                    'label' => 'api.search.form.nights',
                    'choices' => range(1, 100),
                    'required' => true,
                ]);
        } else {
            $builder
                ->add('end', DateType::class, array(
                    'label' => 'api.search.form.end',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'required' => true,
                    'attr' => ['class' => 'datepicker', 'data-date-format' => 'dd.mm.yyyy']
                ));
        }

        $builder
            ->add('hotels', DocumentType::class, [
                'label' => 'api.search.form.hotel',
                'class' => 'MBHHotelBundle:Hotel',
                'required' => false,
                'query_builder' => function (DocumentRepository $dr) use ($hotels) {
                    return $dr->createQueryBuilder('q')
                        ->field('id')->in($this->helper->toIds($hotels))
                        ->field('deletedAt')->equals(null)
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                },
                'empty_data' => count($hotels) > 1 ? [] : $hotels,
                'multiple' => true,
                'property' => 'fullTitle',
                'attr' => ['class' => count($hotels) == 1 ? 'hide' : ''],
                'label_attr' => ['class' => count($hotels) == 1 ? 'hide' : ''],
            ]);

        if ($config->getRoomTypes()) {
            $builder
                ->add('roomTypes', DocumentType::class, [
                    'label' => 'api.search.form.roomtype',
                    'class' => $this->manager->useCategories ? 'MBHHotelBundle:RoomTypeCategory' : 'MBHHotelBundle:RoomType',
                    'required' => false,
                    'query_builder' => function (DocumentRepository $dr) use ($hotels) {
                        return $dr->createQueryBuilder('q')
                            ->field('hotel.id')->in($this->helper->toIds($hotels))
                            ->field('deletedAt')->equals(null)
                            ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                    },
                    'empty_data' => count($hotels) > 1 ? [] : $hotels,
                    'property' => 'fullTitle',
                    'multiple' => true
            ]);

        }
        if ($config->getTourists()) {
            $builder
                ->add('adults', ChoiceType::class, [
                'label' => 'api.search.form.adults',
                'choices' => range(1, 10),
                'required' => true,
            ])
                ->add('children', ChoiceType::class, [
                'label' => 'api.search.form.children',
                'choices' => range(1, 10),
                'required' => true,
            ]);
        };
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Lib\SearchQuery',
            'config' => null
        ]);
    }

    public function getName()
    {
        return 'mbh_api_search_type';
    }

}

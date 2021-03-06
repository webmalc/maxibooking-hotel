<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 05.07.16
 * Time: 14:31
 */

namespace MBH\Bundle\RestaurantBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\RestaurantBundle\Document\Table;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
class TableType extends AbstractType
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    private $container;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->dm = $options['dm'];
        $this->container = $options['container'];

        $selector = $this->container->get('mbh.hotel.selector');
        $tableId = $builder->getData()->getId();

        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'restaurant.table.form.fullTitle.label',
                'required' => true,
                'attr' => ['placeholder' => 'restaurant.table.form.fullTitle.placeholder'],
                'help' => 'restaurant.table.form.fullTitle.help',
                'group' => 'restaurant.group'

            ])
            ->add('title', TextType::class, [
                'label' => 'restaurant.table.form.title.label',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.table.form.title.placeholder'],
                'help' => 'restaurant.table.form.title.help',
                'group' => 'restaurant.group'
            ])
            ->add('withShifted', DocumentType::class, [
                'label' => 'restaurant.table.common.shifted',
                'class' => 'MBH\Bundle\RestaurantBundle\Document\Table',
                'required' => false,
                'multiple' => true,
                'group_by' => 'category',
                'group' => 'restaurant.group',
                'query_builder' => function (DocumentRepository $repository) use ($tableId,$selector) {
                    $hotelId = $selector->getSelected()->getId();
                    $qb = $repository->createQueryBuilder()
                        ->field('hotel.id')->equals($hotelId)
                        ->field('id')->notIn(array($tableId))
                        ->field('isEnabled')->equals(true);
                    return $qb;
                }

            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'restaurant.table.form.is_enable.label',
                'required' => false,
                'value' => false,
                'help' => 'restaurant.table.form.is_enable.help',
                'group' => 'restaurant.group'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\Table',
                'dm' => null,
                'container' => null
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_restaurantbundle_table_type';
    }


}
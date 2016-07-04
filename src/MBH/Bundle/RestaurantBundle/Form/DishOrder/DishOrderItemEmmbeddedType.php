<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 04.07.16
 * Time: 11:38
 */

namespace MBH\Bundle\RestaurantBundle\Form\DishOrder;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishOrderItemEmmbeddedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', TextType::class, [
                'help' => 'Количество',
                'attr' => [
                    'class' => 'amount amount-spinner',
                    'placeholder' => 'restaurant.dishorder.form.amount.placeholder'
                ]
            ])
            ->add('dish_menu_item', DocumentType::class, [
                'class' => 'MBH\Bundle\RestaurantBundle\Document\DishMenuItem',
                'query_builder' => function (DocumentRepository $repository) {
                    return $repository->createQueryBuilder()
                        ->field('isEnabled')
                        ->equals(true);
                },
                'attr' => [
                    'class' => 'plain-html'
                ],
                'group_by' => 'category.name'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\DishOrderItemEmbedded'
            ]);
    }

    public function getName()
    {
        return 'mbh_bundle_restaurantbundle_dishorder_dishitemembedded_type';
    }

}
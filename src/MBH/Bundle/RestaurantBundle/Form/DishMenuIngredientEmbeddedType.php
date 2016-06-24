<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 23.06.16
 * Time: 15:47
 */

namespace MBH\Bundle\RestaurantBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishMenuIngredientEmbeddedType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', TextType::class, [
                'help' => 'Количество',
                'attr' => [
                    'class' => 'amount amount-spinner',
                    'placeholder' => 'Количество ингредиента'
                ],
            ])
            ->add('ingredient', DocumentType::class, [
                'class' => 'MBH\Bundle\RestaurantBundle\Document\Ingredient',
                'attr' => [
                    'class' => 'plain-html'
                ]
            ])
        ;
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\DishMenuIngredientEmbedded'
            ]);
    }

    public function getName()
    {
        return 'mbh_bundle_restaurantbundle_dishmenu_ingredientembedded_type';
    }


}
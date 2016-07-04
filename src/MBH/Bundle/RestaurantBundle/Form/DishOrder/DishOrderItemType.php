<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 04.07.16
 * Time: 11:38
 */

namespace MBH\Bundle\RestaurantBundle\Form\DishOrder;


use MBH\Bundle\RestaurantBundle\Document\DishOrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishOrderItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class, [
                'label' => 'restaurant.dishorder.form.id.label',
                'read_only' => true,
                'required' => false
            ])
            ->add('table', TextType::class, [
                'label' => 'restaurant.dishorder.form.table.label',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.dishorder.form.table.placeholder'],
                'help' => 'restaurant.dishorder.form.table.help'
            ])
            /* mappded  я здесь указываю для того чтобы вызывался метод getPrice  убираю его в PreSubmit чтоб не вызывать метод setPrice */
            ->add('price', TextType::class,[
                'label' => 'restaurant.dishorder.form.price.label',
                'required' => false,
                'disabled' => true,
                'mapped' => true
            ])
            ->add('dishes', CollectionType::class, [
                'entry_type' => DishOrderItemEmmbeddedType::class,
                'label' => 'restaurant.dishorder.form.dishes.label',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                /** @var DishOrderItem $order */
                $order = $event->getData();
                $form = $event->getForm();
                $idOptions = $form->get('id')->getConfig()->getOptions();
                if (!$order->getId()) {
                    $idOptions = array_replace($idOptions, [
                        'mapped' => false,
                        'attr' => [
                            'placeholder' => 'restaurant.dishorder.form.id.noidplaceholder'
                        ]
                    ]);
                    $form->add('id', TextType::class, $idOptions);
                }
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $event->getForm()->remove('price');
            })
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\DishOrderItem'
            ]);
    }

    public function getName()
    {
        return 'mbh_bundle_restaurantbundle_dishorder_dishorderitem_type';
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 04.07.16
 * Time: 11:38
 */

namespace MBH\Bundle\RestaurantBundle\Form\DishOrder;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
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
                'required' => false,
                'group' => 'Основная информация'
            ])
            ->add('table', DocumentType::class, [
                'label' => 'restaurant.dishorder.form.table.label',
                'class' => 'MBH\Bundle\RestaurantBundle\Document\Table',
                'choice_label' => 'name',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.dishorder.form.table.placeholder', 'class' => 'plain-html'],
                'help' => 'restaurant.dishorder.form.table.help',
                'group' => 'Основная информация'
            ])
            /* mappded  я здесь указываю для того чтобы вызывался метод getPrice  убираю его в PreSubmit чтоб не вызывать метод setPrice */
            ->add('price', TextType::class,[
                'label' => 'restaurant.dishorder.form.price.label',
                'required' => false,
                'disabled' => true,
                'mapped' => false,
                'group' => 'Составление заказа'
            ])
            ->add('dishes', CollectionType::class, [
                'entry_type' => DishOrderItemEmmbeddedType::class,
                'label' => 'restaurant.dishorder.form.dishes.label',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'group' => 'Составление заказа'
            ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [
                    $this, 'onPreSetData'
                ]
            )
           ;
    }

    public function onPreSetData(FormEvent $event)
    {
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
    }
    public function onPostSubmitData(FormEvent $event)
    {
        $data = $event->getData();
        return true;
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
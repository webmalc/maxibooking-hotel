<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 04.07.16
 * Time: 11:38
 */

namespace MBH\Bundle\RestaurantBundle\Form\DishOrder;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\RestaurantBundle\Document\DishOrderItem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishOrderItemType extends AbstractType
{
    private $container;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $selector = $this->container->get('mbh.hotel.selector');
        $builder
            ->add('id', TextType::class, [
                'label' => 'restaurant.dishorder.form.id.label',
                'read_only' => true,
                'required' => false,
                'group' => 'restaurant.group'
            ])
            ->add('table', DocumentType::class, [
                'label' => 'restaurant.dishorder.form.table.label',
                'class' => 'MBH\Bundle\RestaurantBundle\Document\Table',
                'query_builder' => function (DocumentRepository $repository) use ($selector) {
                    $hotelId = $selector->getSelected()->getId();
                    return $repository->createQueryBuilder()
                        ->field('hotel.id')->equals($hotelId)
                        ->field('isEnabled')->equals(true);
                },
                'choice_label' => 'name',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.dishorder.form.table.placeholder', 'class' => 'plain-html'],
                'help' => 'restaurant.dishorder.form.table.help',
                'group' => 'restaurant.group'
            ])
            ->add('dishes', CollectionType::class, [
                'entry_type' => DishOrderItemEmmbeddedType::class,
                'label' => 'restaurant.dishorder.form.dishes.label',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'group' => 'restaurant.collectorder'
            ])
            ->add('order', TextType::class, [
                'label' => 'restaurant.dishorder.form.order.label',
                'attr' => [
                    'class' => 'order-select'
                ],
                'required' => false,
                'group' => 'restaurant.descr'


            ])
            /* mappded  я здесь указываю для того чтобы вызывался метод getPrice  убираю его в PreSubmit чтоб не вызывать метод setPrice */
            ->add('price', TextType::class,[
                'label' => 'restaurant.dishorder.form.price.label',
                'required' => false,
                'disabled' => true,
                'mapped' => false,
                'group' => 'restaurant.descr'
            ])
            ->add('isFreezed', CheckboxType::class,[
                'label' => 'restaurant.dishorder.form.isfreezed.label',
                'required' => false,
                'group' => 'restaurant.pay'
                ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [
                    $this, 'onPreSetData'
                ]
            )
           ;

        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $builder->get('order')->addViewTransformer(new EntityToIdTransformer($dm, 'MBHPackageBundle:Package'));
    }

    public function onPreSetData(FormEvent $event)
    {
        /** @var DishOrderItem $order */
        $order = $event->getData();
        if (!$order->getId()) {
            /** @var Form $form */
            $form = $event->getForm();
            $idOptions = $form->get('id')->getConfig()->getOptions();
            $idOptions = array_replace($idOptions, [
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'restaurant.dishorder.form.id.noidplaceholder'
                ]
            ]);
            $form->add('id', TextType::class, $idOptions);
        }
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
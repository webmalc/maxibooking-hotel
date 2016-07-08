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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishOrderItemType extends AbstractType
{
    private $dm;


    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }


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
                'query_builder' => function (DocumentRepository $repository) {
                    return $repository->createQueryBuilder()
                        ->field('isEnabled')
                        ->equals(true);
                },
                'choice_label' => 'name',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.dishorder.form.table.placeholder', 'class' => 'plain-html'],
                'help' => 'restaurant.dishorder.form.table.help',
                'group' => 'Основная информация'
            ])
            ->add('dishes', CollectionType::class, [
                'entry_type' => DishOrderItemEmmbeddedType::class,
                'label' => 'restaurant.dishorder.form.dishes.label',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'group' => 'Составление заказа'
            ])
            ->add('order', TextType::class, [
                'label' => 'restaurant.dishorder.form.order.label',
                'attr' => [
                    'class' => 'order-select'
                ],
                'required' => false,
                'group' => 'Подробности'


            ])
            /* mappded  я здесь указываю для того чтобы вызывался метод getPrice  убираю его в PreSubmit чтоб не вызывать метод setPrice */
            ->add('price', TextType::class,[
                'label' => 'restaurant.dishorder.form.price.label',
                'required' => false,
                'disabled' => true,
                'mapped' => false,
                'group' => 'Подробности'
            ])
            ->add('isFreezed', CheckboxType::class,[
                'label' => 'restaurant.dishorder.form.isfreezed.label',
                'required' => false,
                'group' => 'Оплата'
                ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [
                    $this, 'onPreSetData'
                ]
            )
           ;
        $builder->get('order')->addViewTransformer(new EntityToIdTransformer($this->dm, 'MBHPackageBundle:Package'));
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
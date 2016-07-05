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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishOrderItemEmmbeddedType extends AbstractType
{
    private $requestStack;

    private $current;

    /**
     * DishOrderItemEmmbeddedType constructor.
     * @param $request
     */
    public function __construct(RequestStack $request)
    {
        $this->requestStack = $request;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', TextType::class, [
                'help' => 'Количество',
                'attr' => [
                    'class' => 'amount int-amount-spinner',
                    'placeholder' => 'restaurant.dishorder.form.amount.placeholder'
                ]
            ])
            ->add('dishMenuItem', DocumentType::class, [
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
            ])
            ->addEventListener(
                FormEvents::POST_SET_DATA,
                [
                    $this, 'onPostSetData'
                ]
            )
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                [
                    $this, 'onPostSubmitData'
                ]
            )
        ;
    }
    //Фиксируем цену при создании заказа вызовом метода setPrice()
    //Так же проверяем не изменился ли в коллекции элемент, он сохраняется в методе onPostSetData
    public function onPostSubmitData(FormEvent $event)
    {
        $data = $event->getData();
        $currentDishId = $data->getDishMenuItem()->getId();
        if (!$data->getPrice() || $this->current !== $currentDishId) {
            $data->setPrice();
        }

    }

    public function onPostSetData(FormEvent $event)
    {
        $data = $event->getData();
        if ($data) {
            $this->current = $data->getDishMenuItem()->getId();
        }
    }

    //Нотификация при изменении себестоимости, стоит доработать и выводить только один раз и только для неоплаченных блюд.
    /*public function onPreSetData(FormEvent $event)
    {

        if ($data = $event->getData()) {
            if ($data->getFixedPrice() != $data->getDishMenuItem()->getActualPrice()) {
                $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()
                    ->set('danger', 'Внимание, изменилась себестоимость блюда '.$data->getDishMenuItem()->getName());
            }
        }

    }*/

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
<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 22.06.16
 * Time: 17:13
 */

namespace MBH\Bundle\RestaurantBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishMenuItemType extends AbstractType 
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.fullTitle.label',
                'required' => true,
                'attr' => ['placeholder' => 'restaurant.dishmenu.item.form.fullTitle.placeholder'],
                'help' => 'restaurant.dishmenu.item.form.fullTitle.help',
                'group' => 'Общая информация'

            ])
            ->add('title', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.title.label',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.dishmenu.item.form.title.placeholder'],
                'help' => 'restaurant.dishmenu.item.form.title.help',
                'group' => 'Общая информация'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'restaurant.dishmenu.item.form.description.label',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.dishmenu.item.form.description.placeholder'],
                'help' => 'restaurant.dishmenu.item.form.description.help',
                'group' => 'Общая информация'
            ])
            ->add('is_enabled', CheckboxType::class, [
                'label' => 'restaurant.ingredient.form.is_enable.label',
                'required' => false,
                'value' => true,
                'help' => 'restaurant.ingredient.form.is_enable.help',
                'group' => 'Общая информация'
            ])
            ->add('price', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.price.label',
                'required' => false,
                'attr' => ['class' => 'price-spinner price'],
                'help' => 'restaurant.dishmenu.item.form.price.help',
                'group' => 'Формирование цены'
            ])
            ->add('costPrice', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.costprice.label',
                'required' => false,
                'attr' => ['class' => 'costprice price-spinner', 'disabled'=>true],
                'help' => 'restaurant.dishmenu.item.form.costprice.help',
                'group' => 'Формирование цены'
            ])
            ->add('margin', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.margin.label',
                'required' => false,
                'attr' => [
                    'class' => 'percent-margin'
                ],
                'disabled' => true,
                'group' => 'Формирование цены'
            ])
            ->add('is_margin', CheckboxType::class, [
                'label' => 'Маржа',
                'required' => false,
                'attr' => [
                    'class' => 'is_margin'
                ],
                'group' => 'Формирование цены'
            ])

            ->add('dishIngredients', CollectionType::class, [
                'entry_type' => DishMenuIngredientEmbeddedType::class,
                'label' => 'restaurant.dishmenu.item.form.dishIngredients.label',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => true,
                'required' => false,
                'group' => 'Состав блюда'
            ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [
                    $this, 'onPreSetData'
                ]
            )
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                [
                    $this, 'onPreSubmitData'
                ]
            )
        ;

    }

    //Слушатели здесь потому, что при изменении формы на странице, нужно убирать атрибут disable с поля цена,
    //поле is_enable при обработке формы должно быть включено всегда, не смотря на начальный вывод в форме.
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        if ($event->getData()->getIsMargin()) {
            $this->isMarginEnabledAction($form);
        }
    }

    public function onPreSubmitData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if ($data['is_margin']??false) {
            $this->isMarginEnabledAction($form);
        } else {
            $this->isMarginDisabledAction($form);
        }
    }

    private function isMarginEnabledAction(FormInterface $form)
    {
        $priceOptions = $form->get('price')->getConfig()->getOptions();
        $marginOptions = $form->get('margin')->getConfig()->getOptions();

        $form->add('price', TextType::class, array_replace($priceOptions,['disabled' => true ]));
        $form->add('margin', TextType::class, array_replace($marginOptions, ['disabled' => false]));
    }

    private function isMarginDisabledAction(FormInterface $form)
    {
        $priceOptions = $form->get('price')->getConfig()->getOptions();
        $form->add('price', TextType::class, array_replace($priceOptions,['disabled' => false ]));
    }
    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\DishMenuItem',
                'group' => null
            ]
        );
    }

    
    public function getName()
    {
        return 'mbh_bundle_restaurantbundle_dishmenu_item_type';
    }

}
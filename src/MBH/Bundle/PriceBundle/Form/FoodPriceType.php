<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;

class FoodPriceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tariff = $options['entity'];
        $types = $options['types'];
        $foods = $tariff->getHotel()->getFood();
        $pricesEntites = $tariff->getFoodPrices();
        $prices = [];
        
        foreach ($pricesEntites as $price) {
            $prices[$price->getType()] = $price->getPrice();
        }
        
        foreach ($types as $abbr => $food) {
            if (in_array($abbr, $foods)) {
                
                (isset($prices[$abbr])) ? $data = $prices[$abbr] : $data = null;
                
                $builder
                        ->add($abbr, 'text', [
                            'label' => $food,
                            'required' => false,
                            'attr' => ['placeholder' => 'Не используется в тарифе', 'class' => 'spinner price-spinner'],
                            'data' => $data,
                            'constraints' => new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                        ])
                ;
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'entity' => false, 'types' => []
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_food_price_type';
    }

}

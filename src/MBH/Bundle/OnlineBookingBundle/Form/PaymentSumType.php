<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 10/3/16
 * Time: 3:53 PM
 */

namespace MBH\Bundle\OnlineBookingBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentSumType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sum', NumberType::class, [
                'label' => 'Сумма к оплате'
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'attr' => [
                    'id' => 'payment_sum'
                ]
            ]);
    }

}
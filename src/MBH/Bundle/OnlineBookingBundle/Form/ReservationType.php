<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 10/5/16
 * Time: 4:39 PM
 */

namespace MBH\Bundle\OnlineBookingBundle\Form;


use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ReservationType extends SignType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('reservation', HiddenType::class, [
                'data' => 'true',
                'mapped' => false
            ])
            ->remove('payment')
            ->remove('offerta')
            ;
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        $reservation = $view->children['reservation'];
        /** @var FormView $reservation */
        $reservation->vars['full_name'] = $reservation->vars['name'];
    }


}
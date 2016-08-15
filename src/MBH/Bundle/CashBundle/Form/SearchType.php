<?php

namespace MBH\Bundle\CashBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Class SearchType
 * @package MBH\Bundle\CashBundle\Form

 */
class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', 'date', array(
                'label' => 'C',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'data' => new \DateTime(),
                'required' => true,
                'error_bubbling' => true,
                'attr' => array('class' => 'datepicker begin-datepicker', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()]
            ))
            ->add('end', 'date', array(
                'label' => 'mbhcashbundle.form.searchtype.po',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'data' => null,
                'required' => true,
                'error_bubbling' => true,
                'attr' => array('class' => 'datepicker end-datepicker', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()]
            ))
            ->add('sort', 'choice', [
                'label' => 'mbhcashbundle.form.searchtype.sortirova',
                'required' => false,
            ])
            ->add('pay_type', 'choice', [
                'label' => 'mbhcashbundle.form.searchtype.vidplatezha',
                'required' => false,
            ])
            ->add('show_no_paid', 'checkbox', [
                'required' => false,
            ])
            ->add('by_day', 'checkbox', [
                'required' => false,
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getName()
    {
        return 's';
    }

}
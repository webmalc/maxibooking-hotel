<?php


namespace MBH\Bundle\HotelBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RbkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rbkEshopId', TextType::class, [
                'label' => 'form.clientPaymentSystemType.rbk_eshop_id',
                'group' => 'no-group'
            ])
            ->add('rbkSecretKey', TextType::class, [
                'label' => 'form.clientPaymentSystemType.rbk_secret_key',
                'group' => 'no-group'
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'MBH\Bundle\ClientBundle\Document\Rbk',
            ]);
    }

}
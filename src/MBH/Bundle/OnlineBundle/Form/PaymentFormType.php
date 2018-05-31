<?php
/**
 * Created by PhpStorm.
 * Date: 31.05.18
 */

namespace MBH\Bundle\OnlineBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class PaymentFormType extends AbstractType implements DecorationTypeInterface
{
    public const PREFIX = 'mbh_bundle_onlinebundle_payment_form_type';

    use DecorationTypeTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'hotels',
                DocumentType::class,
                [
                    'label'    => 'form.formType.hotels',
                    'class'    => 'MBH\Bundle\HotelBundle\Document\Hotel',
                    'group'    => 'form.formType.parameters',
                    'required' => false,
                    'multiple' => true,
                    'attr'     => ['placeholder' => 'form.formType.hotels_placeholder'],
                    'help'     => 'form.formType.hotels_desc',
                ]
            )
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label'    => 'form.formType.is_turned_on',
                    'group'    => 'form.formType.parameters',
                    'value'    => true,
                    'required' => false,
                    'help'     => 'form.formType.use_online_form',
                ]
            );

        $builder
            ->add($this->isFullWidth($builder))
            ->add($this->frameWidth($builder))
            ->add($this->frameHeight($builder))
            ->add($this->css($builder))
            ->add($this->isHorizontal($builder))
            ->add($this->cssLibraries($builder))
            ->add($this->theme($builder));
    }

    public function getBlockPrefix()
    {
        return self::PREFIX;
    }
}
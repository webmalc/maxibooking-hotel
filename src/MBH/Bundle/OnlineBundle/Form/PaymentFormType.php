<?php
/**
 * Created by PhpStorm.
 * Date: 31.05.18
 */

namespace MBH\Bundle\OnlineBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
                'isEnabled',
                CheckboxType::class,
                [
                    'label'    => 'form.formType.is_turned_on',
                    'group'    => 'form.formType.parameters',
                    'value'    => true,
                    'required' => false,
                    'help'     => 'form.formType.use_online_form',
                ]
            )
            ->add(
                'fieldUserNameIsVisible',
                CheckboxType::class,
                [
                    'label'    => 'form.payment.formType.fieldUserNameIsVisible',
                    'group'    => 'form.formType.parameters',
                    'required' => false,
                    'help'     => 'form.payment.formType.fieldUserNameIsVisible.help',
                ]
            )
            /** пока неиспользуется */
//            ->add(
//                'enabledShowAmount',
//                CheckboxType::class,
//                [
//                    'label'    => 'form.payment.formType.enabledShowAmount',
//                    'group'    => 'form.formType.parameters',
//                    'required' => false,
//                    'help'     =>  'form.payment.formType.enabledShowAmount_help',
//                ]
//            )
            ->add(
                'useAccordion',
                CheckboxType::class,
                [
                    'label'    => 'form.payment.formType.useAccordion',
                    'group'    => 'form.formType.parameters',
                    'required' => false,
                    'help'     => 'form.payment.formType.useAccordion_help',
                ]
            )
            ->add(
                'enabledReCaptcha',
                CheckboxType::class,
                [
                    'label'    => 'form.payment.formType.enabledReCaptcha',
                    'group'    => 'form.formType.parameters',
                    'required' => false,
                    'help'     => 'form.payment.formType.enabledReCaptcha.help',
                ]
            )
        ;

        $builder
            ->add($this->isFullWidth($builder))
            ->add($this->frameWidth($builder))
            ->add($this->frameHeight($builder))
            ->add($this->css($builder))
            ->add($this->cssLibraries($builder))
            ->add($this->theme($builder));

        $builder
            ->add('js',
                TextareaType::class,
                [
                    'group'    => 'form.formType.js_group',
                    'label'    => 'form.formType.js_label',
                    'required' => false,
                    'attr'     => ['rows' => 10],
                ]);
    }

    public function getBlockPrefix()
    {
        return self::PREFIX;
    }
}
<?php

namespace MBH\Bundle\FMSBundle\Form;

use MBH\Bundle\BaseBundle\Form\Extension\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ImportFMSType
 * @package MBH\Bundle\FMSBundle\Form
 */
class ImportFMSType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, [
                'label' => 'form.ImportFMSType.startDate',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['class' => 'input-small datepicker', 'data-date-format' => 'dd.mm.yyyy'],
            ])
            ->add('endDate', DateType::class, [
                'label' => 'form.ImportFMSType.endDate',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['class' => 'input-small datepicker', 'data-date-format' => 'dd.mm.yyyy'],
            ]);
    }

    public function getName()
    {
        return 'mbh_bundle_fmsbundle_import_main_type';
    }


}
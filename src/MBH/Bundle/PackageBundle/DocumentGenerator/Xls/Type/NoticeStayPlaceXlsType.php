<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Xls\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NoticeStayPlaceXlsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tourist', 'document', [
            'required' => true,
            'class' => 'MBH\Bundle\PackageBundle\Document\Tourist',
            'label' => 'form.task.tourist'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'notice_stay_place_xls';
    }
}
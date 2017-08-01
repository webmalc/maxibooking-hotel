<?php


namespace MBH\Bundle\HotelBundle\Form;


use MBH\Bundle\BaseBundle\Form\ImageType;
use MBH\Bundle\BaseBundle\Form\Traits\ImagePriorityTrait;
use Symfony\Component\Form\FormBuilderInterface;


class OnlineImageFileType extends ImageType
{

    use ImagePriorityTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $this->addPriorityType($builder, $options);
    }


    public function getBlockPrefix()
    {
        return 'mbh_hotel_online_image_form';
    }

}
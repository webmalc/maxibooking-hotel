<?php
/**
 * Created by PhpStorm.
 * Date: 01.06.18
 */

namespace MBH\Bundle\OnlineBundle\Form;


use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Lib\SearchForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderSearchType extends AbstractType
{
    const PREFIX = 'mbh_bundle_onlinebundle_order_search_type';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var SearchForm $search */
        $search = $builder->getData();

        $userNameVisible = $search->isUserNameVisible();

        $commonInputAttr = ['class' => 'form-control input-sm'];
        $commonLabelAttr = ['class' => 'col-form-label col-form-label-sm'];
        $commonGroup = 'form.online.order_search';

        $builder
            ->add(
                'numberPackage',
                TextType::class,
                [
                    'label'      => 'form.online.order_search.numberPackage',
                    'label_attr' => $commonLabelAttr,
                    'group'      => $commonGroup,
                    'attr'       => $commonInputAttr,
                ]
            )
            ->add(
                'phoneOrEmail',
                TextType::class,
                [
                    'label'      => 'form.online.order_search.phoneOrEmail',
                    'label_attr' => $commonLabelAttr,
                    'group'      => $commonGroup,
                    'attr'       => $commonInputAttr,
                ]
            )
            ->add(
                'configId',
                HiddenType::class
            );

        if (!$search->getPaymentFormConfig()->isForMbSite() && count($search->getHotels()) > 1) {
            $hotels = [];

            /** @var Hotel $hotel */
            foreach ($search->getHotels() as $hotel) {
                $hotels[$hotel->getName()] = $hotel->getId();
            }

            $builder->add(
                'hotelId',
                ChoiceType::class,
                [
                    'label'      => 'form.online.order_search.hotel',
                    'choices'    => $hotels,
                    'data'       => $search->getHotelId(),
                    'label_attr' => $commonLabelAttr,
                    'group'      => $commonGroup,
                    'attr'       => $commonInputAttr,
                ]
            );
        } else {
            $builder->add(
                'hotelId',
                HiddenType::class,
                [
                    'data' => $search->getHotelId() ?? $search->getHotels()[0]->getId(),
                ]
            );
        }

        if ($userNameVisible) {
            $builder->add(
                'userName',
                TextType::class,
                [
                    'label'      => 'form.online.order_search.userName',
                    'label_attr' => $commonLabelAttr,
                    'group'      => $commonGroup,
                    'attr'       => $commonInputAttr,
                ]
            );
        }

        $builder
            ->add(
                'findPackage',
                SubmitType::class,
                [
                    'label' => 'form.online.order_search.button_search',
                    'attr'  => ['class' => 'btn btn-primary btn-block'],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }


    public function getBlockPrefix()
    {
        return self::PREFIX;
    }
}
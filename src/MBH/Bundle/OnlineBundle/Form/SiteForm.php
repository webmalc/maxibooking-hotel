<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\HotelRepository;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteForm extends AbstractType
{
    /** @var DocumentManager */
    private $dm;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hotels = $this->dm
            ->getRepository('MBHHotelBundle:Hotel')
            ->getQBWithAvailable()
            ->getQuery()
            ->execute()
            ->toArray();

        /** @var SiteConfig $siteConfig */
        $siteConfig = $builder->getData();
        if (count($hotels) === 1 && !$siteConfig && $siteConfig->getHotels()->count() === 0) {
            $siteConfig->addHotel(current($hotels));
        }

        //TODO: ФОРМА ОТРИСОВЫВАЕТСЯ В ШАБЛОНЕ ВРУЧНУЮ
        $builder
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'site_form.is_enabled.label',
                'required' => false,
                'attr' => [
                    'class' => 'box-full-visibility-checkbox'
                ],
            ])
            ->add('siteDomain', TextType::class, [
                'label' => 'Адрес',
                'required' => true,
            ])
            ->add('keyWords', CollectionType::class, [
                'label' => 'site_form.key_words.label',
                'required' => false,
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('contract', TextareaType::class, [
                'label' => 'site_form.contract.label',
                'attr' => ['class' => 'tinymce'],
                'required' => false
            ])
            ->add('personalDataPolicies', TextareaType::class, [
                'label' => 'site_form.pers_data_policy.label',
                'attr' => ['class' => 'tinymce'],
                'required' => false
            ])
            ->add('paymentTypes', PaymentTypesType::class, [
                'mapped' => false,
                'help' => 'form.formType.reservation_payment_types_with_online_form'
            ])
            ->add('hotels', DocumentType::class, [
                'label' => 'site_form.hotels.label',
                'class' => Hotel::class,
                'multiple' => true,
                'required' => false,
                'help' => 'site_form.hotels.help',
                'query_builder' => function(HotelRepository $hotelRepository) {
                    return $hotelRepository->getQBWithAvailable();
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => SiteConfig::class
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhonline_bundle_site_form';
    }
}

<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\HotelRepository;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Services\SiteManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Common\Collections\Collection;

class SiteForm extends AbstractType
{
    /** @var DocumentManager */
    private $dm;
    private $translator;
    private $siteManager;

    public function __construct(DocumentManager $dm, TranslatorInterface $translator, SiteManager $siteManager) {
        $this->dm = $dm;
        $this->translator = $translator;
        $this->siteManager = $siteManager;
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
        if (count($hotels) === 1 && $siteConfig && $siteConfig->getHotels()->count() === 0) {
            $siteConfig->addHotel(current($hotels));
        }

        //ФОРМА ОТРИСОВЫВАЕТСЯ В ШАБЛОНЕ ВРУЧНУЮ!
        $builder
            ->add('siteDomain', TextType::class, [
                'label' => 'site_form.web_address.label',
                'required' => true,
                'addonText' => SiteManager::SITE_DOMAIN,
                'preAddonText' => SiteManager::SITE_PROTOCOL,
                'help' => $siteConfig->getSiteDomain()
                    ? '<a class="btn btn-success" target="_blank" href="' . $this->siteManager->getSiteAddress() . '">'
                    . $this->translator->trans('site_form.site_domain.go_to_site_button.text'). '</a>'
                    : ''
            ])
            ->add('keyWords', CollectionType::class, [
                'label' => 'site_form.key_words.label',
                'required' => false,
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
//            ->add('contract', TextareaType::class, [
//                'label' => 'site_form.contract.label',
//                'attr' => ['class' => 'tinymce'],
//                'required' => false,
//                'help' => 'sdfasdfasdf'
//            ])
                // отдельный Type для политики
//            ->add('personalDataPolicies', TextareaType::class, [
//                'label' => 'site_form.pers_data_policy.label',
//                'attr' => ['class' => 'tinymce'],
//                'required' => false,
//            ])
            ->add('paymentTypes', PaymentTypesType::class, [
                'mapped' => false,
                'help' => 'form.formType.reservation_payment_types_with_online_form',
                'constraints' => [new NotBlank()]
            ])
            /**
             * для select2 в colorTheme используется margin-bottom
             * src/MBH/Bundle/OnlineBundle/Resources/public/css/mb-site/mb-site.css
             * вместо help
             */
            ->add('colorTheme', ChoiceType::class, [
                'label' => 'site_config.color_theme.colors.label',
                'choices' => array_keys(SiteConfig::COLORS_BY_THEMES),
                'choice_label' => function(string $theme) {
                    return 'site_config.color_theme.colors.' . $theme;
                }
            ])
            ->add('hotels', DocumentType::class, [
                'label' => 'site_form.hotels.label',
                'class' => Hotel::class,
                'multiple' => true,
                'required' => true,
                'help' => 'site_form.hotels.help',
                'query_builder' => function(HotelRepository $hotelRepository) {
                    return $hotelRepository->getQBWithAvailable();
                },
                'constraints' => [new Callback(function (Collection $data, ExecutionContextInterface $context) {
                    if ($data->isEmpty()) {
                        $context->addViolation('validator.site_form.hotels_collection_is_empty');
                    }
                })]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => SiteConfig::class
            ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options); // TODO: Change the autogenerated stub

    }

    public function getBlockPrefix()
    {
        return 'mbhonline_bundle_site_form';
    }
}

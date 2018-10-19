<?php

namespace MBH\Bundle\HotelBundle\Form\HotelFlow;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Service\MBHFormBuilder;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\HotelRepository;
use MBH\Bundle\HotelBundle\Form\ContactInfoType;
use MBH\Bundle\HotelBundle\Form\LogoImageType;
use MBH\Bundle\HotelBundle\Service\FlowManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class HotelFlowType extends AbstractType
{
    private $mbhFormBuilder;
    private $router;
    private $flowManager;
    private $dm;

    public function __construct(MBHFormBuilder $mbhFormBuilder, Router $router, FlowManager $flowManager, DocumentManager $dm) {
        $this->mbhFormBuilder = $mbhFormBuilder;
        $this->router = $router;
        $this->flowManager = $flowManager;
        $this->dm = $dm;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Hotel $hotel */
        $hotel = $builder->getData();

        switch ($options['flow_step']) {
            case HotelFlow::HOTEL_STEP:
                $builder
                    ->add(
                        'hotel', DocumentType::class, [
                            'label' => 'room_type_flow_type.hotel.label',
                            'required' => true,
                            'class' => Hotel::class,
                            'query_builder' => function (HotelRepository $repository) {
                                return $repository->getQBWithAvailable();
                            },
                            'expanded' => true,
                            'multiple' => false
                        ]
                    );
                break;
            case HotelFlow::DESC_STEP:
                $this->mbhFormBuilder->addMultiLangField($builder, TextareaType::class, 'description', [
                    'attr' => ['class' => 'tinymce'],
                    'label' => 'form.hotelType.description',
                    'group' => 'no-group',
                    'required' => false
                ]);
                break;
            case HotelFlow::LOGO_STEP:
                $builder->add('logoImage', LogoImageType::class, [
                    'label' => 'form.hotel_logo.image_file.help',
                    'group' => 'form.hotelType.settings',
                    'required' => false,
                    'logo_image_download_url' => $this->getDownloadUrl($hotel->getLogoImage()),
                    'logo_image_delete_url' => $this->router->generate('hotel_delete_logo_image', [
                        'id' => $hotel->getId(),
                        'redirect_url' => $this->router->generate('mb_flow', ['type' => HotelFlow::FLOW_TYPE])
                    ])
                ]);
                break;
            case HotelFlow::CONTACTS_STEP:
                $builder->add('contactInformation', ContactInfoType::class, [
                    'group' => 'no-group',
                    'hasGroups' => false
                ]);
                break;
            case HotelFlow::MAIN_PHOTO_STEP:
                $builder->add('defaultImage', LogoImageType::class, [
                    'label' => 'Главная',
                    'group' => 'form.hotelType.settings',
                    'required' => false,
                    'logo_image_download_url' => $this->getDownloadUrl($hotel->getDefaultImage()),
                    'showHelp' => false
                ]);
                break;
            default:
                throw new \InvalidArgumentException('Incorrect flow step number!');
        }
    }

    /**
     * @param Image|null $image
     * @return null|string
     */
    private function getDownloadUrl(?Image $image)
    {
        return !is_null($image)
            ? $this->router->generate('hotel_logo_download', ['id' => $image->getId()])
            : null;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['flow_step'] === HotelFlow::CONTACTS_STEP) {
            $view->children['contactInformation']->vars['embedded'] = true;
        } elseif ($options['flow_step'] === HotelFlow::HOTEL_STEP) {
            $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->getEnabled();
            $hotelIds = array_map(function (Hotel $roomType) {
                return $roomType->getId();
            }, $hotels);
            $progressRates = $this->flowManager->getProgressRateByFlowIds(HotelFlow::FLOW_TYPE, array_values($hotelIds));
            $view->children['hotel']->vars['flowProgressRates'] = $progressRates;
            $view->children['hotel']->vars['selectedId'] = null;
        }
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_hotel_flow';
    }
}
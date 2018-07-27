<?php


namespace MBH\Bundle\SearchBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\HotelRepository;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomTypesType extends AbstractType
{
    /** @var bool */
    private $isUseCategory;
    /**
     * @var HotelRepository
     */
    private $hotelRepository;

    public function __construct(ClientConfigRepository $configRepository, HotelRepository $hotelRepository)
    {
        $this->isUseCategory = $configRepository->fetchConfig()->getUseRoomTypeCategory();
        $this->hotelRepository = $hotelRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $className = $options['class'];
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($className) {
                $data = $event->getData();
                if (is_iterable($data)) {
                    foreach ($data as $index => $roomType) {
                        if (false !== mb_stripos($roomType, 'allrooms_')) {
                            $hotelId = explode('_', $roomType)[1];
                            /** @var Hotel $hotel */
                            $dm = $this->hotelRepository->getDocumentManager();
                            $roomTypeIds = $dm->createQueryBuilder($className)
                                ->field('hotel.id')->equals($hotelId)
                                ->distinct('id')
                                ->getQuery()
                                ->toArray()
                            ;
                            $data[$index] = array_map('\strval', $roomTypeIds);

                        } else {
                            $data[$index] = (array)$roomType;
                        }
                    }
                    $data = array_merge(...$data);
                    $event->setData(array_unique($data));
                }


            }
        );

    }


    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $choices = $view->vars['choices'] ?? null;
        if (\is_iterable($choices)) {
            foreach (array_keys($choices) as $hotelName) {
                $currentChoices = $choices[$hotelName]->choices;
                $isAllRoomsExists = (bool)\count(array_filter($currentChoices, function ($choiceView) {
                    /** @var ChoiceView $choiceView */
                    return $choiceView->data === 'fakeData';
                }));
                if (!$isAllRoomsExists) {
                    $hotelId = $this->hotelRepository->findOneBy(['title' => $hotelName])->getId();
                    $choiceView = new ChoiceView('fakeData', 'allrooms_'.$hotelId, 'Все номера');
                    array_unshift($choices[$hotelName]->choices, $choiceView);
                }

            }
            uasort($choices, function($hotelNameA, $hotelNameB) {
                /** @var Hotel $hotelA */
                $hotelA = $this->hotelRepository->findOneBy(['title' => $hotelNameA->label]);
                /** @var Hotel $hotelB */
                $hotelB = $this->hotelRepository->findOneBy(['title' => $hotelNameB->label]);

                return $hotelB->getIsDefault() <=> $hotelA->getIsDefault();
            });
            $view->vars['choices'] = $choices;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => $this->isUseCategory ? RoomTypeCategory::class : RoomType::class,
            'required' => false,
            'multiple' => true,
            'error_bubbling' => true,
            'group_by' => function ($roomTypeOrCategory) {
                /** @var RoomTypeCategory|RoomType $roomTypeOrCategory */
                return $roomTypeOrCategory->getHotel()->getName();
            },
            'query_builder' => function (DocumentRepository $dr) {
                $hotelIds = $dr
                    ->getDocumentManager()
                    ->getRepository(Hotel::class)
                    ->getSearchActiveIds();

                return $dr->createQueryBuilder()->field('hotel.id')->in($hotelIds)->sort('title', 'asc');
            },
            'choice_label' => 'name'
        ]);
    }


    public function getParent(): string
    {
        return DocumentType::class;
    }

}
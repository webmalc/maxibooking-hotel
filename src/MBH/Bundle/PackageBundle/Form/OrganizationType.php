<?php

namespace MBH\Bundle\PackageBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date as ConstrainDate;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class OrganizationType
 *

 */
class OrganizationType extends AbstractType
{
    const SCENARIO_NEW = 'new';
    const SCENARIO_EDIT = 'edit';

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $scenario = $options['scenario'];
        $isFull = $options['isFull'];
        $id = $options['id'];

        if (!$isFull) {
            $builder->add('organization', TextType::class, [
                'group' => 'form.organizationType.group.search',
                'label' => 'form.organizationType.name',
                'required' => false,
                'mapped' => false,
            ]);
        }

        if($options['imageUrl']) {
            $logoHelp = '<br><a href="'.$options['imageUrl'].'" class="fancybox">Просмотреть изображение</a>';
        } else {
            $logoHelp = '';
        }

        $personalGroup = 'form.organizationType.group.personal';
        $addGroup = 'form.organizationType.group.add';
        $registerGroup = 'form.organizationType.group.registration';
        $additionalGroup = 'form.organizationType.group.additional';
        $locationGroup = 'form.organizationType.group.location';
        $checkAccountGroup = 'form.organizationType.group.check_account';

        $group = $isFull ? $personalGroup : $addGroup;

        $builder->add('name', TextType::class, [
            'group' => $group,
            'label' => 'form.organizationType.name',
        ]);
        if ($isFull) {
            $builder->add('short_name', TextType::class, [
                'group' => $personalGroup,
                'label' => 'form.organizationType.short_name',
                'required' => false,
            ]);
            $builder->add('director_fio', TextType::class, [
                'group' => $personalGroup,
                'label' => 'form.organizationType.director_fio',
                'required' => false,
            ]);
            $builder->add('accountant_fio', TextType::class, [
                'group' => $personalGroup,
                'label' => 'form.organizationType.accountant_fio',
                'required' => false,
            ]);
        }
        $builder->add('phone', TextType::class, [
            'group' => $group,
            'label' => 'form.organizationType.phone',
            'attr' => ['class' => 'input-small'],
            'required' => false,
        ]);

        $builder->add('email', EmailType::class, [
            'group' => $group,
            'label' => 'form.organizationType.email',
            'required' => false,
        ]);

        $builder->add('inn', TextType::class, [
            'group' => $group,
            'label' => 'form.organization.inn.label',
            'attr' => ['class' => 'input-small'],
            'translation_domain' => 'individual'
        ]);
        $builder->add('kpp', TextType::class, [
            'group' => $isFull ? $registerGroup : $addGroup,
            'label' => 'form.organizationType.kpp',
            'attr' => ['class' => 'input-small'],
            'required' => false
        ]);

        if ($isFull) {
            $builder->add('registration_date', DateType::class, [
                'widget' => 'single_text',
                'group' => $registerGroup,
                'label' => 'form.organizationType.registration_date',
                'required' => false,
                'format' => 'dd.MM.yyyy',
                'constraints' => [new ConstrainDate()],
                'attr' => ['class' => 'input-small'],
            ]);
            $builder->add('registration_number', TextType::class, [
                'group' => $registerGroup,
                'label' => 'form.organizationType.registration_number',
                'required' => false,
                'attr' => ['class' => 'input-small'],
            ]);
            $builder->add('activity_code', TextType::class, [
                'group' => $registerGroup,
                'label' => 'form.organizationType.activity_code',
                'required' => false,
                'attr' => ['class' => 'input-small'],
            ]);
            $builder->add('okpo_code', TextType::class, [
                'group' => $registerGroup,
                'label' => 'form.organizationType.okpo_code',
                'required' => false,
                'attr' => ['class' => 'input-small'],
            ]);
            $builder->add('writer_fio', TextType::class, [
                'group' => $registerGroup,
                'label' => 'form.organizationType.writer_fio',
                'required' => false,
            ]);
            $builder->add('reason', TextType::class, [
                'group' => $registerGroup,
                'label' => 'form.organizationType.reason',
                'required' => false,
            ]);
        }

        $group = $isFull ? $locationGroup : $addGroup;

        $builder->add('city', TextType::class, [ //'document', [
            'group' => $group,
            'label' => 'form.organizationType.city',
            'attr' => ['placeholder' => 'form.hotelExtendedType.placeholder_location', 'class' => 'citySelect'],
        ]);

        $builder->get('city')->addModelTransformer(new EntityToIdTransformer($this->documentManager,
            'MBHHotelBundle:City'));

        $builder->add('street', TextType::class, [
            'group' => $group,
            'label' => 'form.organizationType.street',
            'required' => false
        ]);
        $builder->add('house', TextType::class, [
            'group' => $group,
            'label' => 'form.organizationType.house',
            'attr' => ['class' => 'input-xs'],
            'required' => false
        ]);

        if ($isFull) {
            $builder->add('corpus', TextType::class, [
                'group' => $locationGroup,
                'label' => 'form.organizationType.corpus',
                'required' => false,
                'attr' => ['class' => 'input-xs'],
            ]);
            $builder->add('flat', TextType::class, [
                'group' => $locationGroup,
                'label' => 'form.organizationType.flat',
                'required' => false,
                'attr' => ['class' => 'input-xs'],
            ]);
            $builder->add('index', TextType::class, [
                'group' => $locationGroup,
                'label' => 'form.organizationType.index',
                'required' => false,
                'attr' => ['class' => 'input-xs'],
            ]);
            $builder->add('bank', TextType::class, [
                'group' => $checkAccountGroup,
                'label' => 'form.organizationType.bank',
                'required' => false,
            ]);
            $builder->add('bank_bik', TextType::class, [
                'group' => $checkAccountGroup,
                'label' => 'form.organizationType.bank_bik',
                'required' => false,
                'attr' => ['class' => 'input-small'],
            ]);
            $builder->add('bank_address', TextType::class, [
                'group' => $checkAccountGroup,
                'label' => 'form.organizationType.bank_address',
                'required' => false,
            ]);
            $builder->add('correspondent_account', TextType::class, [
                'group' => $checkAccountGroup,
                'label' => 'form.organizationType.correspondent_account',
                'required' => false,
            ]);
            $builder->add('checking_account', TextType::class, [
                'group' => $checkAccountGroup,
                'label' => 'form.organizationType.checking_account',
                'required' => false,
            ]);
        }

        if ($isFull) {
            if ($scenario == self::SCENARIO_NEW) {
                $builder->add('type', ChoiceType::class, [
                    'group' => $isFull ? $additionalGroup : $addGroup,
                    'label' => 'form.organizationType.type',
                    'choices' => $options['typeList'],
                ]);
            }

            $hotelsOptions = [
                'class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
                'label' => 'form.organizationType.default_hotels',
                'help' => 'form.organizationType.default_hotels_help',
                'multiple' => true,
                'required' => false,
                'query_builder' => function (DocumentRepository $hotelRepository) use ($id) {
                    $queryBuilder = $this->documentManager->getRepository('MBHPackageBundle:Organization')->createQueryBuilder();
                    $queryBuilder
                        ->select('hotels')
                        ->field('type')->equals('my');
                    if ($id) {
                        $queryBuilder->field('id')->notEqual($id);
                    }

                    $organizations = $queryBuilder->getQuery()->execute();
                    $exceptHotelIDs = [];
                    foreach ($organizations as $organization) {
                        if (count($organization->getHotels())) {
                            foreach ($organization->getHotels() as $hotel) {
                                $exceptHotelIDs = array_merge($exceptHotelIDs, [$hotel->getId()]);
                            }

                        }
                    }

                    $queryBuilder = $hotelRepository->createQueryBuilder();
                    if ($exceptHotelIDs) {
                        $queryBuilder->field('id')->notIn(array_unique($exceptHotelIDs));
                    }

                    return $queryBuilder;
                },
            ];

            $hotelsOptions['group'] = $isFull ? $additionalGroup : $addGroup;
            if($scenario == self::SCENARIO_NEW || ($scenario == self::SCENARIO_EDIT && $options['type'] == 'my')) {
                $builder->add('hotels', DocumentType::class, $hotelsOptions);
            }
        }

        if ($isFull) {
            $builder->add('comment', TextareaType::class, [
                'group' => $additionalGroup,
                'label' => 'form.organizationType.comment',
                'required' => false,
                'constraints' => [new Length(['min' => 2, 'max' => 300])]
            ]);

            $builder->add('stamp', FileType::class, [
                'group' => $additionalGroup,
                'label' => 'form.organizationType.stamp',
                'required' => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Image([
                        /*'minWidth' => 400,
                        'maxWidth' => 400,
                        'maxHeight' => 200,
                        'minHeight' => 200,*/
                    ])
                ],
                'help' => 'Скан печати для генерации документов (400x200 пикселей)' . $logoHelp
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'typeList' => [],
            'id' => null,
            'type' => null,
            'scenario' => self::SCENARIO_NEW,
            'isFull' => true,
            'imageUrl' => null
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'organization';
    }

}
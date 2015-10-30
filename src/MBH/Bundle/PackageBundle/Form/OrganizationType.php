<?php

namespace MBH\Bundle\PackageBundle\Form;


use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Date as ConstrainDate;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class OrganizationType
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
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
            $builder->add('organization', 'text', [
                'group' => 'form.organizationType.group.search',
                'label' => 'form.organizationType.name',
                'required' => false,
                'mapped' => false,
            ]);
        }

        $personalGroup = 'form.organizationType.group.personal';
        $addGroup = 'form.organizationType.group.add';
        $registerGroup = 'form.organizationType.group.registration';
        $additionalGroup = 'form.organizationType.group.additional';
        $locationGroup = 'form.organizationType.group.location';
        $checkAccountGroup = 'form.organizationType.group.check_account';

        $group = $isFull ? $personalGroup : $addGroup;

        $builder->add('name', 'text', [
            'group' => $group,
            'label' => 'form.organizationType.name',
        ]);
        if ($isFull) {
            $builder->add('short_name', 'text', [
                'group' => $personalGroup,
                'label' => 'form.organizationType.short_name',
                'required' => false,
            ]);
            $builder->add('director_fio', 'text', [
                'group' => $personalGroup,
                'label' => 'form.organizationType.director_fio',
                'required' => false,
            ]);
            $builder->add('accountant_fio', 'text', [
                'group' => $personalGroup,
                'label' => 'form.organizationType.accountant_fio',
                'required' => false,
            ]);
        }
        $builder->add('phone', 'text', [
            'group' => $group,
            'label' => 'form.organizationType.phone',
            'attr' => ['class' => 'input-small'],
        ]);

        $builder->add('email', 'email', [
            'group' => $group,
            'label' => 'form.organizationType.email',
            'required' => false,
        ]);

        $builder->add('inn', 'text', [
            'group' => $group,
            'label' => 'form.organizationType.inn',
            'attr' => ['class' => 'input-small'],
        ]);
        $builder->add('kpp', 'text', [
            'group' => $isFull ? $registerGroup : $addGroup,
            'label' => 'form.organizationType.kpp',
            'attr' => ['class' => 'input-small'],
        ]);

        if ($isFull) {
            $builder->add('registration_date', 'date', [
                'widget' => 'single_text',
                'group' => $registerGroup,
                'label' => 'form.organizationType.registration_date',
                'required' => false,
                'format' => 'dd.MM.yyyy',
                'constraints' => [new ConstrainDate()],
                'attr' => ['class' => 'input-small'],
            ]);
            $builder->add('registration_number', 'text', [
                'group' => $registerGroup,
                'label' => 'form.organizationType.registration_number',
                'required' => false,
                'attr' => ['class' => 'input-small'],
            ]);
            $builder->add('activity_code', 'text', [
                'group' => $registerGroup,
                'label' => 'form.organizationType.activity_code',
                'required' => false,
                'attr' => ['class' => 'input-small'],
            ]);
            $builder->add('okpo_code', 'text', [
                'group' => $registerGroup,
                'label' => 'form.organizationType.okpo_code',
                'required' => false,
                'attr' => ['class' => 'input-small'],
            ]);
            $builder->add('writer_fio', 'text', [
                'group' => $registerGroup,
                'label' => 'form.organizationType.writer_fio',
                'required' => false,
            ]);
            $builder->add('reason', 'text', [
                'group' => $registerGroup,
                'label' => 'form.organizationType.reason',
                'required' => false,
            ]);
        }

        $group = $isFull ? $locationGroup : $addGroup;

        $builder->add('city', 'text', [ //'document', [
            'group' => $group,
            'label' => 'form.organizationType.city',
            'attr' => ['placeholder' => 'form.hotelExtendedType.placeholder_location', 'class' => 'citySelect'],
        ]);

        $builder->get('city')->addModelTransformer(new EntityToIdTransformer($this->documentManager,
            'MBHHotelBundle:City'));

        $builder->add('street', 'text', [
            'group' => $group,
            'label' => 'form.organizationType.street',
        ]);
        $builder->add('house', 'text', [
            'group' => $group,
            'label' => 'form.organizationType.house',
            'attr' => ['class' => 'input-xs'],
        ]);

        if ($isFull) {
            $builder->add('corpus', 'text', [
                'group' => $locationGroup,
                'label' => 'form.organizationType.corpus',
                'required' => false,
                'attr' => ['class' => 'input-xs'],
            ]);
            $builder->add('flat', 'text', [
                'group' => $locationGroup,
                'label' => 'form.organizationType.flat',
                'required' => false,
                'attr' => ['class' => 'input-xs'],
            ]);
            $builder->add('index', 'text', [
                'group' => $locationGroup,
                'label' => 'form.organizationType.index',
                'required' => false,
                'attr' => ['class' => 'input-xs'],
            ]);
            $builder->add('bank', 'text', [
                'group' => $checkAccountGroup,
                'label' => 'form.organizationType.bank',
                'required' => false,
            ]);
            $builder->add('bank_bik', 'text', [
                'group' => $checkAccountGroup,
                'label' => 'form.organizationType.bank_bik',
                'required' => false,
                'attr' => ['class' => 'input-small'],
            ]);
            $builder->add('bank_address', 'text', [
                'group' => $checkAccountGroup,
                'label' => 'form.organizationType.bank_address',
                'required' => false,
            ]);
            $builder->add('correspondent_account', 'text', [
                'group' => $checkAccountGroup,
                'label' => 'form.organizationType.correspondent_account',
                'required' => false,
            ]);
            $builder->add('checking_account', 'text', [
                'group' => $checkAccountGroup,
                'label' => 'form.organizationType.checking_account',
                'required' => false,
            ]);
        }

        if ($isFull) {
            if ($scenario == self::SCENARIO_NEW) {
                $builder->add('type', 'choice', [
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
                    foreach ($organizations->getMongoCursor() as $organization) {
                        if (array_key_exists('hotels', $organization)) {
                            $exceptHotelIDs += array_column($organization['hotels'], '$id');
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
                $builder->add('hotels', 'document', $hotelsOptions);
            }
        }

        if ($isFull) {
            $builder->add('comment', 'textarea', [
                'group' => $additionalGroup,
                'label' => 'form.organizationType.comment',
                'required' => false,
                'constraints' => [new Length(['min' => 2, 'max' => 300])]
            ]);

            $builder->add('stamp', 'file', [
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
                'help' => 'Скан печати для генерации документов (400x200 пикселей)'
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
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'organization';
    }

}
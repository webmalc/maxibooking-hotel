<?php

namespace MBH\Bundle\PackageBundle\Form;


use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Date as ConstrainDate;

/**
 * Class OrganizationType
 * @package MBH\Bundle\PackageBundle\Form
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class OrganizationType extends AbstractType
{
    private $documentManager;

    public function __construct(\Doctrine\ODM\MongoDB\DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', [
            'group' => 'Личные данные',
            'label' => 'form.organizationType.name',
        ]);
        $builder->add('short_name', 'text', [
            'group' => 'Личные данные',
            'label' => 'form.organizationType.short_name',
            'required' => false,
        ]);
        $builder->add('director_fio', 'text', [
            'group' => 'Личные данные',
            'label' => 'form.organizationType.director_fio',
            'required' => false,
        ]);
        $builder->add('phone', 'text', [
            'group' => 'Личные данные',
            'label' => 'form.organizationType.phone',
            'attr' => ['class' => 'input-small'],
        ]);
        $builder->add('email', 'email', [
            'group' => 'Личные данные',
            'label' => 'form.organizationType.email',
            'required' => false,
        ]);

        $builder->add('inn', 'text', [
            'group' => 'Регистрационные данные',
            'label' => 'form.organizationType.inn',
            'attr' => ['class' => 'input-small'],
            //'constraints' => [new Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique(['fields' => 'inn'])],
        ]);
        $builder->add('kpp', 'text', [
            'group' => 'Регистрационные данные',
            'label' => 'form.organizationType.kpp',
            'attr' => ['class' => 'input-small'],
        ]);
        $builder->add('registration_date', 'date', [
            'widget' => 'single_text',
            'group' => 'Регистрационные данные',
            'label' => 'form.organizationType.registration_date',
            'required' => false,
            'format' => 'dd.MM.yyyy',
            'constraints' => [new ConstrainDate()],
            'attr' => ['class' => 'input-small'],
        ]);
        $builder->add('registration_number', 'text', [
            'group' => 'Регистрационные данные',
            'label' => 'form.organizationType.registration_number',
            'required' => false,
            'attr' => ['class' => 'input-small'],
        ]);
        $builder->add('activity_code', 'text', [
            'group' => 'Регистрационные данные',
            'label' => 'form.organizationType.activity_code',
            'required' => false,
            'attr' => ['class' => 'input-small'],
        ]);
        $builder->add('okpo_code', 'text', [
            'group' => 'Регистрационные данные',
            'label' => 'form.organizationType.okpo_code',
            'required' => false,
            'attr' => ['class' => 'input-small'],
        ]);
        $builder->add('writer_fio', 'text', [
            'group' => 'Регистрационные данные',
            'label' => 'form.organizationType.writer_fio',
            'required' => false,
        ]);
        $builder->add('reason', 'text', [
            'group' => 'Регистрационные данные',
            'label' => 'form.organizationType.reason',
            'required' => false,
        ]);

        $builder->add('city', 'text', [ //'document', [
            'group' => 'form.organizationType.group_location',
            'label' => 'form.organizationType.city',
            //'class' => 'MBHHotelBundle:City',
            'attr' => ['placeholder' => 'form.hotelExtendedType.placeholder_location'],
            //'property' => 'generateFullNameWithAge',
            /*'query_builder' => function(DocumentRepository $er) use($touristIds) {
                return $er->createQueryBuilder()->field('_id')->in($touristIds);
            },*/
        ]);

        $builder->get('city')->addModelTransformer(new EntityToIdTransformer($this->documentManager, 'MBHHotelBundle:City'));

        $builder->add('street', 'text', [
            'group' => 'form.organizationType.group_location',
            'label' => 'form.organizationType.street',
        ]);
        $builder->add('house', 'text', [
            'group' => 'form.organizationType.group_location',
            'label' => 'form.organizationType.house',
            'attr' => ['class' => 'input-xs'],
        ]);
        $builder->add('corpus', 'text', [
            'group' => 'form.organizationType.group_location',
            'label' => 'form.organizationType.corpus',
            'required' => false,
            'attr' => ['class' => 'input-xs'],
        ]);
        $builder->add('flat', 'text', [
            'group' => 'form.organizationType.group_location',
            'label' => 'form.organizationType.flat',
            'required' => false,
            'attr' => ['class' => 'input-xs'],
        ]);
        $builder->add('index', 'text', [
            'group' => 'form.organizationType.group_location',
            'label' => 'form.organizationType.index',
            'required' => false,
            'attr' => ['class' => 'input-xs'],
        ]);

        $builder->add('bank', 'text', [
            'group' => 'Расчётный счёт',
            'label' => 'form.organizationType.bank',
            'required' => false,
        ]);
        $builder->add('bank_bik', 'text', [
            'group' => 'Расчётный счёт',
            'label' => 'form.organizationType.bank_bik',
            'required' => false,
            'attr' => ['class' => 'input-small'],
        ]);
        $builder->add('bank_address', 'text', [
            'group' => 'Расчётный счёт',
            'label' => 'form.organizationType.bank_address',
            'required' => false,
        ]);
        $builder->add('checking_account', 'text', [
            'group' => 'Расчётный счёт',
            'label' => 'form.organizationType.checking_account',
            'required' => false,
        ]);
        $builder->add('type', 'choice', [
            'group' => 'Дополнительно',
            'label' => 'form.organizationType.type',
            'attr' => ['class' => 'input-small'],
            'choices' => $options['typeList'],
        ]);

        $id = $options['id'];

        $builder->add('hotels', 'document', [
            'group' => 'Дополнительно',
            'class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
            'label' => 'form.organizationType.default_hotels',
            'help' => 'form.organizationType.default_hotels_help',
            'multiple' => true,
            'required' => false,
            'query_builder' => function(DocumentRepository $dr) use ($id) {
                /** @var \Doctrine\ODM\MongoDB\Cursor $organizations */
                $queryBuilder = $this->documentManager->getRepository('MBHPackageBundle:Organization')->createQueryBuilder()->select('hotels')->field('type')->equals('my');
                if($id)
                    $queryBuilder->field('id')->notEqual($id);
                $organizations = $queryBuilder->getQuery()->execute();
                $hotelIds = [];
                foreach($organizations->getMongoCursor() as $organization)
                    if(array_key_exists('hotels', $organization))
                        $hotelIds += array_column($organization['hotels'], '$id');

                $hotelIds = array_unique($hotelIds);

                $queryBuilder = $dr->createQueryBuilder('q');
                if($hotelIds)
                    $queryBuilder->field('id')->notIn($hotelIds);

                return $queryBuilder;
            },
        ]);
        $builder->add('comment', 'textarea', [
            'group' => 'Дополнительно',
            'label' => 'form.organizationType.comment',
            'required' => false,
            'constraints' => [new Length(['min' => 2, 'max' => 300])]
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'typeList' => [],
            'id' => null,
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
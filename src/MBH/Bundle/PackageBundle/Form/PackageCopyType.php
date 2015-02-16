<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;

class PackageCopyType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('package', 'document', [
                    'label' => 'Бронь',
                    'group' => 'Параметры переноса',
                    'class' => 'MBHPackageBundle:Package',
                    'required' => true,
                    'property' => 'numberWithPayer',
                    'query_builder' => function(DocumentRepository $er) {
                        return $er->createQueryBuilder('q')
                            ->field('end')->gte(new \DateTime('midnight'))
                            ->sort('createdAt', 'desc');
                    },
                    'empty_value' => '',
                    'help' => 'Бронь в которую будет произведен перенос данных.',
                    'constraints' => [
                        new NotBlank(['message' => 'Не выбрана бронь для переноса'])
                    ]
                ])
            ->add('tourists', 'checkbox', [
                    'label' => 'Гости',
                    'group' => 'Параметры переноса',
                    'value' => true,
                    'required' => false,
                    'help' => 'Перенести ли гостей в выбранную бронь?'
                ])
            ->add('services', 'checkbox', [
                    'label' => 'Услуги',
                    'group' => 'Параметры переноса',
                    'value' => true,
                    'required' => false,
                    'help' => 'Перенести ли все услуги в выбранную бронь?'
                ])
            ->add('accommodation', 'checkbox', [
                    'label' => 'Размещение',
                    'group' => 'Параметры переноса',
                    'value' => true,
                    'required' => false,
                    'help' => 'Перенести ли размещение в выбранную бронь?'
                ])
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_copy_type';
    }

}

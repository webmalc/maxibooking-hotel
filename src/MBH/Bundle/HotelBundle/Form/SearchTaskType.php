<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/22/16
 * Time: 11:32 AM
 */

namespace MBH\Bundle\HotelBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\BaseBundle\Form\Extension\DateType;
use MBH\Bundle\UserBundle\Document\Group;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchTaskType extends AbstractType
{
    private $container;

    /**
     * SearchTaskType constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $statuses = $this->container->getParameter('mbh.task.statuses');

        $builder
            ->add('begin', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker input-sm begin-datepicker'],
            ])
            ->add('end', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker input-sm end-datepicker'],
            ])
            ->add('dateCriteriaType', ChoiceType::class, [
                'empty_value' => '',
                'choices' => [ 'date' => 'form.searchTask.performance', 'createdAt' => 'form.searchTask.creation'],
                'data' => 'date',
            ])
            ->add('status', ChoiceType::class, [
                'empty_value' => '',
                'choices' => array_combine(array_keys($statuses),array_keys($statuses)),
                'choice_label' => function ($status) {
                    return 'task.filter.task.'. $status;
                }
            ])
            ->add('priority', ChoiceType::class, [
                'empty_value' => '',
                'choices' => $options['priority'],
                'choice_label' => function ($index) use ($options){
                    return 'task.filter.prior.'. $options['priority'][$index];
                }
            ])
            ->add('userGroups', DocumentType::class, [
                'empty_value' => '',
                'multiple' => true,
                'class' => Group::class
            ])
            ->add('performer', DocumentType::class, [
                'empty_value' => '',
                'class' => User::class
            ])
            ->add('deleted', CheckboxType::class)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria',
                'priority' => $this->container->getParameter('mbh.tasktype.priority')
            ]);
    }


}
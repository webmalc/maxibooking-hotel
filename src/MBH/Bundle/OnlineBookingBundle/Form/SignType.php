<?php

namespace MBH\Bundle\OnlineBookingBundle\Form;


use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\PriceBundle\Document\Promotion;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class SignType extends AbstractType
{
    private $container;

    private $dm;

    /**
     * SignType constructor.
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine.odm.mongodb.document_manager');
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $paymentTypesConfig = $this->container->getParameter('mbh.online.form')['payment_types'];
//        unset($paymentTypes['online_first_day']);
        $allowed = ['online_full', 'online_half'];
        $paymentTypes = array_filter($paymentTypesConfig, function ($key) use ($allowed) {
            return in_array($key, $allowed);
        }, ARRAY_FILTER_USE_KEY);

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Имя',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Фамилия',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('patronymic', TextType::class, [
                'required' => false,
                'label' => 'Отчество'
            ])
            ->add('phone', TextType::class, [
                'label' => 'Телефон'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Email(),
                    new NotBlank()
                ]
            ])
            ->add('accept', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotNull(),
                    new NotBlank()
                ],
                'label' => 'Я согласен на обработку моих персональных данных.'
            ])
            ->add('offerta', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotNull(),
                    new NotBlank()
                ],
                'label' => 'Принимаю условия договора оферты'
            ])
            //->add('step', 'hidden', [])
            ->add('adults', HiddenType::class, [])
            ->add('children', HiddenType::class, [])
            ->add('begin', HiddenType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('end', HiddenType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('roomType', HiddenType::class, [])
            ->add('tariff', HiddenType::class, [])
            ->add('payment', ChoiceType::class, [
                'label' => 'Сумма оплаты',
                'choices' => $paymentTypes,
                'expanded' => true
            ])
            ->add('total', HiddenType::class)
            ->add('promotion', HiddenType::class);

        $builder->get('promotion')->addViewTransformer(new EntityToIdTransformer($this->dm, Promotion::class));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'csrf_protection' => false,
                'method' => Request::METHOD_GET
            ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form';
    }


}
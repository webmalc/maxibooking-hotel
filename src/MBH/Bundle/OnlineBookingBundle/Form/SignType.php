<?php

namespace MBH\Bundle\OnlineBookingBundle\Form;


use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\OnlineBookingBundle\Controller\DefaultController;
use MBH\Bundle\PriceBundle\Document\Promotion;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
        $allowedPayment = DefaultController::ALLOWED_ONLINE_PAYMENT;
        $paymentTypes = array_filter($this->container->getParameter('mbh.online.form')['payment_types'], function ($key) use ($allowedPayment) {
            return in_array($key, $allowedPayment);
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
                ],
                'required' => false
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
                'label' => 'Принимаю условия <a href="https://yadi.sk/i/thkaoWkPoVMxK" target="_blank">договора-оферты.</a>',
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
            //Вывод суммы через форму, не через яваскрипт как было
            ->add('payment', ChoiceType::class, [
                'label' => 'Сумма оплаты',
                'choices' => $paymentTypes,
                'choice_label' => function ($currentPaymentType) use ($paymentTypes, $options) {
                    $sum = $this->countDiscount($currentPaymentType, $options['total']);
                    return $paymentTypes[$currentPaymentType].' '.$sum.' руб.';
                }
            ])
            ->add('onlinePayment', HiddenType::class)
            ->add('total', HiddenType::class)
            ->add('promotion', HiddenType::class)
        ;
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options){
            $data = $event->getData();
            $paymentType = $data['payment'];
            $sum = $this->countDiscount($paymentType, $options['total']);
            $data['onlinePayment'] = $sum;
            $event->setData($data);


        });
        $builder->get('promotion')->addViewTransformer(new EntityToIdTransformer($this->dm, Promotion::class));
    }

    private function countDiscount($currentPaymentType,$total)
    {
        $percent = explode("_", $currentPaymentType)[1];
        return round($total * $percent / 100);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'csrf_protection' => false,
                'method' => Request::METHOD_GET,
                'total' => 0
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
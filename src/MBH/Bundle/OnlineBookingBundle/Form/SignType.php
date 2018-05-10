<?php

namespace MBH\Bundle\OnlineBookingBundle\Form;


use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\OnlineBookingBundle\Controller\DefaultController;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Lib\PaymentType;
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
                    new Email()
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
                'label' => 'Принимаю условия <a href="'.$options['offeraUrl'].'" target="_blank">договора-оферты.</a>',
            ])
            ->add('cash', TextType::class, [
                'disabled' => true,
                'required' => false,
                'label' => 'Сумма к оплате согласно тарифа',
                'mapped' => false
            ])
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
            ->add('paymentType', HiddenType::class, [])
            ->add('total', HiddenType::class)
            ->add('promotion', HiddenType::class)
            ->add('special', HiddenType::class)
            ->add('savedQueryId', HiddenType::class)
        ;
        $builder->get('cash')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event){
            $data = $event->getForm()->getParent()->getData();
            if ($paymentType = $data['paymentType']) {
                $total = $data['total'];
                $percent = PaymentType::PAYMENT_TYPE_LIST[$paymentType]['value'];
                $event->setData($total/100*$percent.' руб. ('.$percent.'% от '.$total.' руб.)');

            }

        });
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
                'method' => Request::METHOD_GET,
                'offeraUrl' => $this->container->getParameter('offera')
            ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'form';
    }


}
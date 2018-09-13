<?php

namespace MBH\Bundle\ClientBundle\Form;

use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Form\PaymentSystem\PaymentSystemType;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\ExtraData;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\HolderNamePaymentSystem;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ClientPaymentSystemType extends AbstractType
{
    const FORM_NAME = 'mbh_bundle_clientbundle_client_payment_system_type';

    const COMMON_ATTR_CLASS = 'payment-system-params';
    const COMMON_GROUP = 'form.clientPaymentSystemType.payment_system_group';

    /**
     * @var ExtraData
     */
    private $extraData;

    public function __construct(ExtraData $extraData)
    {
        $this->extraData = $extraData;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ClientConfig $clientConfig */
        $clientConfig = $builder->getData();
        $paymentSystemName = $options['paymentSystemName'];

        $paymentSystemsChoices =
            array_filter(
                $this->extraData->getPaymentSystems(),
                function ($paymentSystemName) use ($clientConfig, $options) {
                    return !in_array($paymentSystemName,
                            $clientConfig->getPaymentSystems()) || $paymentSystemName == $options['paymentSystemName'];
                },
                ARRAY_FILTER_USE_KEY
            );

        $isPaymentSystemChanged = $paymentSystemName !== null;

        if ($isPaymentSystemChanged) {
            $holder = $this->extraData->getPaymentSystemAsObj($paymentSystemName);
            $builder
                ->add(
                    'paymentSystemFake',
                    TextType::class,
                    [
                        'label'       => 'form.clientPaymentSystemType.payment_system',
                        'group'       => self::COMMON_GROUP,
                        'data'        => $holder->getName(),
                        'required'    => true,
                        'mapped'      => false,
                        'attr'        => ['readonly' => true],
                    ]
                )
                ->add(
                    'paymentSystem',
                    HiddenType::class,
                    [
                        'data'        => $holder->getKey(),
                        'mapped'      => false,
                        'attr'        => ['readonly' => true],
                    ]
                );
        } else {
            $builder
                ->add(
                    'paymentSystem',
                    InvertChoiceType::class,
                    [
                        'label'       => 'form.clientPaymentSystemType.payment_system',
                        'choices'     => $paymentSystemsChoices,
                        'group'       => self::COMMON_GROUP,
                        'placeholder' => '',
                        'data'        => $paymentSystemName,
                        'required'    => true,
                        'mapped'      => false,
                        'constraints' => [new NotBlank()],
                        'attr'        => ['disabled' => $isPaymentSystemChanged],
                    ]
                );
        }

        /** @var HolderNamePaymentSystem $holder */
        foreach ($this->extraData->getPaymentSystemsAsObj() as $holder) {
            /** @var PaymentSystemType $className */
            $className = "MBH\Bundle\ClientBundle\Form\PaymentSystem\\" . $holder->getName() . "Type";
            if (!class_exists($className)) {
                throw new \Exception(sprintf('A class with a form for "%s" payment system was not found.',
                    $holder->getName()));
            }

            /** @var PaymentSystemDocument $src */
            $src = $className::getSourceDocument();
            $getter = 'get' . $src::fileClassName();

            $optionsBuilder = [
                'group'             => self::COMMON_GROUP,
                'validation_groups' => $paymentSystemName === $holder->getKey() ? null : false,
                'data'              => $clientConfig->$getter() ?? $src,
                'attr'              => [
                    'class'     => 'paymentSystem',
                    'data-name' => $holder->getKey(),
                ],
            ];

            if ($paymentSystemName !== null && $paymentSystemName !== $holder->getKey()) {
                $optionsBuilder['disabled'] = true;
                $optionsBuilder['attr']['style'] = 'display: none;';
            }

            $builder->add($holder->getKey(), $className, $optionsBuilder);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => ClientConfig::class,
            'paymentSystemName' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return self::FORM_NAME;
    }
}

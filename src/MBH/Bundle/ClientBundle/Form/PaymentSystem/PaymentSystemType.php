<?php
/**
 * Created by PhpStorm.
 * Date: 24.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\ExtraData;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\FiscalizationInterface;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\TaxMapInterface;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

abstract class PaymentSystemType extends AbstractType
{

    /** @var ExtraData  */
    protected $extraData;

    /**
     * @var ClientConfig
     */
    protected $clientConfig;

    public function __construct(ExtraData $extraData, ClientConfigManager $clientConfigManager)
    {
        $this->extraData = $extraData;
        $this->clientConfig = $clientConfigManager->fetchConfig();
    }

    /**
     * @return PaymentSystemDocument
     */
    abstract public static function getSourceDocument(): PaymentSystemDocument ;

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['embedded'] = true;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        /** @var PaymentSystemDocument $class */
        $class = static::getSourceDocument();

        $resolver->setDefaults([
            'data_class' => $class::className(),
        ]);
    }

    /**
     * @return ClientConfig
     */
    protected function getClientConfig(): ClientConfig
    {
        return $this->clientConfig;
    }

    /**
     * @return ExtraData
     */
    protected function getExtraData(): ExtraData
    {
        return $this->extraData;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function addCommonAttributes(array $data = [], bool $requiredInFronend = true): array
    {
        $common = [
            'group'       => 'no-group',
            'required'    => false,
        ];

        if ($requiredInFronend) {
            $common['attr'] = ['data-required' => true];
        }

        /**
         * атрибут дисеблед пока встречается только в группе при фискализации
         */
        if (!(isset($data['attr']['disabled']) && $data['attr']['disabled'] === true)) {
            $common['constraints'] = [new NotBlank()];
        }

        return array_merge_recursive($data, $common);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param TaxMapInterface|null $doc
     * @param bool $fieldTaxationRateCode
     * @param bool $fieldTaxationSystemCode
     */
    protected function addFieldsForFiscalization(
        FormBuilderInterface $builder,
        TaxMapInterface $doc = null,
        bool $fieldTaxationRateCode = true,
        bool $fieldTaxationSystemCode = true
    ): void
    {
        $formModifier = function (
            FormEvent $event,
            TaxMapInterface $doc = null,
            bool $fieldTaxationRateCode = true,
            bool $fieldTaxationSystemCode = true
        ) {

            $requiredInFronend = true;
            /** @var FormBuilderInterface $form */
            $form = $event->getForm();
            if ($event->getData() instanceof FiscalizationInterface) {
//                $disabledTaxion = !$event->getData()->isWithFiscalization();
                $disabledTaxion = false;
                $requiredInFronend = $event->getData()->isWithFiscalization();
            } else {
                $disabledTaxion = empty($event->getData()['isWithFiscalization']);
                $attr = [
                    'disabled' => $disabledTaxion,
                ];
            }

            $attr['class'] = 'select_tax_code';

            $form->add(
                'isWithFiscalization',
                CheckboxType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.is_with_fiscalization.label',
                        'attr'  => [
                            'class' => 'checkboxForIsWithFiscalization',
                            'disabled' => $disabledTaxion,
                        ],
                    ],
                    $requiredInFronend
                )
            );

            if ($fieldTaxationRateCode) {
                $form->add(
                    'taxationRateCode',
                    InvertChoiceType::class,
                    $this->addCommonAttributes(
                        [
                            'label'   => 'form.clientPaymentSystemType.taxation_rate_code',
                            'choices' => $this->getExtraData()->getTaxationRateCodes($doc),
                            'attr'    => $attr
                        ],
                        $requiredInFronend
                    )
                );
            }

            if ($fieldTaxationSystemCode) {
                $form->add(
                    'taxationSystemCode',
                    InvertChoiceType::class,
                    $this->addCommonAttributes(
                        [
                            'label'   => 'form.clientPaymentSystemType.taxation_system_code',
                            'choices' => $this->getExtraData()->getTaxationSystemCodes($doc),
                            'attr'    => $attr
                        ],
                        $requiredInFronend
                    )
                );
            }
        };


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier, $doc, $fieldTaxationRateCode, $fieldTaxationSystemCode) {
                $formModifier($event, $doc, $fieldTaxationRateCode, $fieldTaxationSystemCode);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier, $doc, $fieldTaxationRateCode, $fieldTaxationSystemCode) {
                $formModifier($event, $doc, $fieldTaxationRateCode, $fieldTaxationSystemCode);
            }
        );
    }
}
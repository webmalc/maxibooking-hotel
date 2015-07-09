<?php

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateGenerator;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilder;


/**
 * Class DocumentTemplateGeneratorFactory
 * Create Template Generator by type
 *
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class DocumentTemplateGeneratorFactory implements ContainerAwareInterface
{
    const TYPE_CONFIRMATION = 'confirmation';
    const TYPE_CONFIRMATION_EN = 'confirmation_en';
    const TYPE_REGISTRATION_CARD = 'registration_card';
    const TYPE_FMS_FORM_5 = 'fms_form_5';
    const TYPE_EVIDENCE = 'evidence';
    const TYPE_FORM_1_G = 'form_1_g';
    const TYPE_RECEIPT = 'receipt';
    const TYPE_ACT = 'act';

    protected $container;

    protected function getExtendedTypes()
    {
        return [
            self::TYPE_CONFIRMATION => 'ConfirmationTemplateGenerator',
            self::TYPE_CONFIRMATION_EN => 'EnConfirmationTemplateGenerator',
            self::TYPE_REGISTRATION_CARD => 'RegistrationCardTemplateGenerator',
            self::TYPE_FMS_FORM_5 => 'RegistrationCardTemplateGenerator',
            self::TYPE_EVIDENCE => 'RegistrationCardTemplateGenerator',
            self::TYPE_FORM_1_G => 'RegistrationCardTemplateGenerator',
            self::TYPE_RECEIPT => 'RegistrationCardTemplateGenerator',
            self::TYPE_ACT => 'ConfirmationTemplateGenerator',
        ];
    }

    /**
     * @return array
     */
    public static function getAvailableTypes()
    {
        return [
            self::TYPE_CONFIRMATION,
            self::TYPE_CONFIRMATION_EN,
            self::TYPE_REGISTRATION_CARD,
            self::TYPE_FMS_FORM_5,
            self::TYPE_EVIDENCE,
            self::TYPE_FORM_1_G,
            self::TYPE_RECEIPT,
            self::TYPE_ACT
        ];
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param $type
     * @return \Symfony\Component\Form\Form
     */
    public function createFormByType($type)
    {
        /** @var FormBuilder $formBuilder */
        $formBuilder = $this->container->get('form.factory')->createBuilder('form');
        if ($type == self::TYPE_CONFIRMATION_EN ||$type == self::TYPE_CONFIRMATION || $type == self::TYPE_ACT) {
            $formBuilder
                ->add('hasFull', 'checkbox', [
                    'required' => false,
                    'label' => 'templateDocument.form.confirmation.hasFull'
                ])
                ->add('hasServices', 'checkbox', [
                    'required' => false,
                    'label' => 'templateDocument.form.confirmation.hasServices'
                ])
                ->add('hasStamp', 'checkbox', [
                    'required' => false,
                    'label' => 'templateDocument.form.confirmation.hasStamp'
                ]);
        }

        return $formBuilder->getForm();
    }

    /**
     * @param $type
     * @return bool
     */
    public function hasForm($type)
    {
        return $type == self::TYPE_CONFIRMATION_EN ||$type == self::TYPE_CONFIRMATION || $type == self::TYPE_ACT;
    }

    /**
     * Create TemplateGenerator by type
     *
     * @param $type
     * @return DefaultDocumentTemplateGenerator
     */
    public function createGeneratorByType($type)
    {
        $availableTypes = $this->getAvailableTypes();
        if (!in_array($type, $availableTypes)) {
            throw new \InvalidArgumentException();
        }

        $generator = $this->isExtendType($type) ?
            $this->createExtendType($type) :
            $this->createByDefault($type);

        $generator->setContainer($this->container);

        return $generator;
    }

    /**
     * @param $type
     * @return bool
     */
    private function isExtendType($type)
    {
        $extendedTypes = $this->getExtendedTypes();
        return array_key_exists($type, $extendedTypes);
    }

    /**
     * @param $type
     * @return DefaultDocumentTemplateGenerator
     */
    private function createExtendType($type)
    {
        $extendedTypes = $this->getExtendedTypes();
        $className = __NAMESPACE__ . '\\' . 'Extended' . '\\' . $extendedTypes[$type];
        /** @var DefaultDocumentTemplateGenerator $generator */
        $generator = new $className($type);

        return $generator;
    }

    private function createByDefault($type)
    {
        return new DefaultDocumentTemplateGenerator($type);
    }
}
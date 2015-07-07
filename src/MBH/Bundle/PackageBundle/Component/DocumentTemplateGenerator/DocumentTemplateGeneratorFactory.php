<?php

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateGenerator;


/**
 * Class DocumentTemplateGeneratorFactory
 * Create Template Generator by type
 *
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class DocumentTemplateGeneratorFactory
{
    const TYPE_CONFIRMATION = 'confirmation';
    const TYPE_REGISTRATION_CARD = 'registration_card';
    const TYPE_FMS_FORM_5 = 'fms_form_5';
    const TYPE_EVIDENCE = 'evidence';
    const TYPE_FORM_1_G = 'form_1_g';
    const TYPE_RECEIPT = 'receipt';
    const TYPE_ACT = 'act';

    protected function getExtendedTypes()
    {
        return [
            self::TYPE_CONFIRMATION => 'ConfirmationTemplateGenerator',
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
            self::TYPE_REGISTRATION_CARD,
            self::TYPE_FMS_FORM_5,
            self::TYPE_EVIDENCE,
            self::TYPE_FORM_1_G,
            self::TYPE_RECEIPT,
            self::TYPE_ACT
        ];
    }

    /**
     * Create TemplateGenerator by type
     *
     * @param $type
     * @return DefaultDocumentTemplateGenerator
     */
    public function createByType($type)
    {
        $availableTypes = $this->getAvailableTypes();
        if(!in_array($type, $availableTypes)) {
            throw new \InvalidArgumentException();
        }

        if($this->isExtendType($type)) {
            return $this->createExtendType($type);
        }

        return $this->createByDefault($type);
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
        $className = __NAMESPACE__.'\\'.'Extended'.'\\'.$extendedTypes[$type];
        /** @var DefaultDocumentTemplateGenerator $generator */
        $generator = new $className($type);
        return $generator;
    }

    private function createByDefault($type)
    {
        return new DefaultDocumentTemplateGenerator($type);
    }
}
<?php

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateGenerator;

/**
 * Class DocumentTemplateGeneratorFactory
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class DocumentTemplateGeneratorFactory
{
    const TYPE_ACT = 'act';
    const TYPE_CONFIRMATION = 'confirmation';
    const TYPE_EVIDENCE = 'evidence';
    const TYPE_FORM = 'form';
    const TYPE_RECEIPT = 'receipt';
    const TYPE_REGISTRATION_CARD = 'registration_card';

    protected function getExtendedTypes()
    {
        return [
            'confirmation' => 'ConfirmationTemplateGenerator'
        ];
    }

    /**
     * @return array
     */
    public static function getAvailableTypes()
    {
        return [
            self::TYPE_ACT,
            self::TYPE_CONFIRMATION,
            self::TYPE_EVIDENCE,
            self::TYPE_FORM,
            self::TYPE_RECEIPT,
            self::TYPE_REGISTRATION_CARD
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
        /*$availableTypes = $this->getAvailableTypes();
        if(in_array($type, $availableTypes)) {
            throw new \InvalidArgumentException();
        }*/

        $extendedTypes = $this->getExtendedTypes();
        if(array_key_exists($type, $extendedTypes)) {
            $className = __NAMESPACE__.'\\'.'Extended'.'\\'.$extendedTypes[$type];
            /** @var DefaultDocumentTemplateGenerator $Generator */
            $generator = new $className($type);
            return $generator;
        }

        return $this->createByDefault($type);
    }

    private function createByDefault($type)
    {
        return new DefaultDocumentTemplateGenerator($type);
    }
}
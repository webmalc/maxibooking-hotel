<?php
/**
 * Date: 19.03.19
 */

namespace MBH\Bundle\OnlineBundle\Lib\Site;


use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;

class FormConfigDecoratorForMBSite implements \JsonSerializable
{
    /**
     * @var FormConfig
     */
    private $formConfig;

    /**
     * FormConfigDecoratorForMBSite constructor.
     * @param FormConfig $formConfig
     */
    public function __construct(FormConfig $formConfig)
    {
        $this->formConfig = $formConfig;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->formConfig->getId(),
            'width' => $this->formConfig->isFullWidth()
                ? '100%'
                : $this->formConfig->getFrameWidth(). 'px',
            'height' => $this->formConfig->getFrameHeight() . 'px',
        ];
    }


}
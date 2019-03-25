<?php
/**
 * Date: 19.03.19
 */

namespace MBH\Bundle\OnlineBundle\Lib\Site;


use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Services\DataForSearchForm;

class FormConfigDecoratorForMBSite implements \JsonSerializable
{
    /**
     * @var FormConfig
     */
    private $formConfig;

    /**
     * @var DataForSearchForm
     */
    private $dataSearchForm;

    /**
     * FormConfigDecoratorForMBSite constructor.
     * @param FormConfig $formConfig
     */
    public function __construct(FormConfig $formConfig, DataForSearchForm $dataForSearchForm)
    {
        $this->formConfig = $formConfig;
        $this->dataSearchForm = $dataForSearchForm->setFormConfig($formConfig);
    }

    public function jsonSerialize()
    {
        $config = [
            'id'               => $this->formConfig->getId(),
            'loadAllIframeUrl' => $this->dataSearchForm->getUrlForScriptLoadAllIframe(),
        ];


        return $config;
    }


}
<?php
/**
 * Date: 14.05.19
 */

namespace MBH\Bundle\OnlineBundle\Services\ParamConverter;


use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfigManager;
use MBH\Bundle\OnlineBundle\Exception\FormConfig\NotFoundFormConfigException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class FormConfigConverter implements ParamConverterInterface
{
    private const PARAM_NAME_FORM_CONFIG_ID = 'formConfigId';
    private const NAME_FOR_VARIABLE = 'formConfig';

    /**
     * @var FormConfigManager
     */
    private $formConfigManager;

    /**
     * FormConfigConverter constructor.
     * @param FormConfigManager $formConfigManager
     */
    public function __construct(FormConfigManager $formConfigManager)
    {
        $this->formConfigManager = $formConfigManager;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        $key = $options[self::PARAM_NAME_FORM_CONFIG_ID] ?? self::PARAM_NAME_FORM_CONFIG_ID;

        $formConfigId = $request->attributes->get($key, null);

        if ($formConfigId === null) {
            throw new NotFoundFormConfigException('Not found online form id in params.');
        }

        $formConfig = $this->formConfigManager->findOneById($formConfigId);

        if ($formConfig === null) {
            throw new NotFoundFormConfigException();
        }

        $request->attributes->set($configuration->getName() ?? self::NAME_FOR_VARIABLE, $formConfig);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'form_config_converter';
    }
}

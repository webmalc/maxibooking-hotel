<?php
/**
 * Date: 23.05.19
 */

namespace MBH\Bundle\OnlineBundle\Services\ParamConverter;


use MBH\Bundle\OnlineBundle\Exception\NotFoundConfigPaymentFormException;
use MBH\Bundle\OnlineBundle\Services\PaymentForm\PaymentFormManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class PaymentFormConfigConverter implements ParamConverterInterface
{
    private const PARAM_NAME_FORM_CONFIG_ID = 'formConfigId';
    private const NAME_FOR_VARIABLE = 'formConfig';

    /**
     * @var PaymentFormManager
     */
    private $formConfigManager;

    public function __construct(PaymentFormManager $formConfigManager)
    {
        $this->formConfigManager = $formConfigManager;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        $key = $options[self::PARAM_NAME_FORM_CONFIG_ID] ?? self::PARAM_NAME_FORM_CONFIG_ID;

        $formConfigId = $request->attributes->get($key, null);

        if ($formConfigId === null) {
            throw new NotFoundConfigPaymentFormException('Not found online form id in params.');
        }

        $formConfig = $this->formConfigManager->findOneById($formConfigId);

        if ($formConfig === null) {
            throw new NotFoundConfigPaymentFormException();
        }

        $request->attributes->set($configuration->getName() ?? self::NAME_FOR_VARIABLE, $formConfig);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'payment_form_config_converter';
    }
}

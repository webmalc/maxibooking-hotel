<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BillingBundle\Service\BillingResponseHandler;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class FormDataHandler
{
    protected $translator;
    protected $helper;
    protected $multiLangTranslator;
    protected $configManager;

    public function __construct(TranslatorInterface $translator, Helper $helper, MultiLangTranslator $multiLangTranslator, ClientConfigManager $configManager)
    {
        $this->translator = $translator;
        $this->helper = $helper;
        $this->multiLangTranslator = $multiLangTranslator;
        $this->configManager = $configManager;
    }

    /**
     * @param $data
     * @param $fields
     * @return array
     */
    public function getUnsetFields($data, $fields)
    {
        return array_filter($fields, function ($fieldName) use ($data) {
            return !isset($data[$fieldName]);
        });
    }

    /**
     * @param $data
     * @param $keysInFilledArrayByKeysInDataArray
     * @param array $excludedFields
     * @return array
     */
    public function fillArrayByKeys($data, $keysInFilledArrayByKeysInDataArray, $excludedFields = [])
    {
        $result = [];
        foreach ($data as $itemKey => $dataItem) {
            if (in_array($itemKey, $excludedFields)) {
                $result[$itemKey] = $dataItem;
            } elseif (isset($keysInFilledArrayByKeysInDataArray[$itemKey])) {
                $filledArrayKey = $keysInFilledArrayByKeysInDataArray[$itemKey];
                $result[$filledArrayKey] = $dataItem;
            } else {
                throw new \InvalidArgumentException('Key for data key "' . $itemKey . '" is not specified!' );
            }
        }

        return $result;
    }

    /**
     * @param FormInterface $form
     * @param array $errors
     */
    public function fillFormByBillingErrors(FormInterface $form, array $errors)
    {
        foreach ($errors as $fieldName => $errorMessages) {
            foreach ($errorMessages as $errorMessage) {
                if ($form->has($fieldName)) {
                    $form->get($fieldName)->addError(new FormError($errorMessage));
                } elseif ($fieldName === BillingResponseHandler::NON_FIELD_ERRORS) {
                    $form->addError(new FormError($errorMessage));
                } else {
                    throw new \InvalidArgumentException('Passed error for nonexistent field');
                }
            }
        }
    }

    /**
     * @param array $data
     * @param array $callbacksByFieldKeys
     * @return array
     */
    public function convertArrayDataByCallbacks(array $data, array $callbacksByFieldKeys)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = isset($callbacksByFieldKeys[$key]) ? $callbacksByFieldKeys[$key]($value) : $value;
        }

        return $result;
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param array $multipleLanguagesFields
     */
    public function saveTranslationsFromMultipleFieldsForm(FormInterface $form, Request $request, array $multipleLanguagesFields)
    {
        if (!$this->configManager->hasSingleLanguage()) {
            $translationsByFields
                = $this->helper->getFromArrayByKeys($request->request->get($form->getName()), $multipleLanguagesFields);
            $this->multiLangTranslator->saveByMultiLanguagesFields($form->getData(), $translationsByFields);
        }
    }
}
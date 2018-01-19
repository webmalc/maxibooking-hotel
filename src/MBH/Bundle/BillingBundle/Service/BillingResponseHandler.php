<?php

namespace MBH\Bundle\BillingBundle\Service;

use MBH\Bundle\BillingBundle\Lib\Model\Result;
use Symfony\Component\Translation\TranslatorInterface;

class BillingResponseHandler
{
    const NON_FIELD_ERRORS = 'non_field_errors';
    private $supportContacts;
    private $translator;

    public function __construct(array $supportContacts, TranslatorInterface $translator) {
        $this->supportContacts = $supportContacts;
        $this->translator = $translator;
    }

    /**
     * @param $requestResult
     * @return array
     */
    private function getErrorsByFailedRequestResult($requestResult): array
    {
        $errors = $requestResult->getErrors();
        if (empty($errors)) {
            $errors['non_field_errors'] = [$this->translator->trans('interactive_login_listener.error_by_client_confirmation', [
                '%supportEmail%' => $this->supportContacts['email']
            ])];
        } else {
            $this->fillErrorsByNestedFields('ru', $errors);
            $this->fillErrorsByNestedFields('world', $errors);
        }

        return $errors;
    }

    /**
     * @param Result $requestResult
     * @return array
     */
    public function getErrorsByRequestResult(Result $requestResult): array
    {
        if (!$requestResult->isSuccessful()) {
            return $this->getErrorsByFailedRequestResult($requestResult);
        }

        return [];
    }

    /**
     * @param string $nestedArrayFieldName
     * @param array $errors
     */
    private function fillErrorsByNestedFields(string $nestedArrayFieldName, array &$errors)
    {
        if (isset($errors[$nestedArrayFieldName])) {
            foreach ($errors[$nestedArrayFieldName] as $fieldName => $fieldErrors) {
                $errors[$fieldName] = $fieldErrors;
            }
            unset($errors[$nestedArrayFieldName]);
        }
    }
}
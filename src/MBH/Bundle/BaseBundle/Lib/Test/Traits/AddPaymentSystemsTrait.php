<?php
/**
 * Created by PhpStorm.
 * Date: 26.09.18
 */

namespace MBH\Bundle\BaseBundle\Lib\Test\Traits;


use MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\HolderNamePaymentSystem;
use Symfony\Component\DomCrawler\Crawler;

trait AddPaymentSystemsTrait
{
    private function newValueFormField(): string
    {
        return '123';
    }

    private function getUrlForAddPaymentSystem(): string
    {
       return '/management/client/config/payment_system/form';
    }

    /**
     * Возращает массив с записанным данными
     *
     * @param HolderNamePaymentSystem $holder
     * @return array
     */
    private function addToClientConfigPaymentSystem(HolderNamePaymentSystem $holder): array
    {
        $crawler = $this->getListCrawler($this->getUrlForAddPaymentSystem());

        $dataForForm = $this->getDataFromForm($crawler, $holder, $this->newValueFormField());

        $form = $crawler->filter('button[name="save"]')
            ->form(
                [
                    ClientPaymentSystemType::FORM_NAME . '[paymentSystem]' => $holder->getKey(),
                ]
            );

        $form->setValues($dataForForm);

        // сабмитим форму
        $this->client->submit($form);

        return $dataForForm;
    }

    /**
     * @param Crawler $crawler
     * @param HolderNamePaymentSystem $holder
     * @param string|null $writeData
     * @return array
     */
    private function getDataFromForm(Crawler $crawler, HolderNamePaymentSystem $holder, string $writeData = null): array
    {
        // данные для формы
        $nameFields = $crawler->filter(
            '[name^="' . ClientPaymentSystemType::FORM_NAME . '[' . $holder->getKey() . ']"]'
        );

        $dataForm = [];

        foreach ($nameFields as $field) {
            if (!empty($field->getAttribute('disabled'))) {
                continue;
            }

            $attr = $field->getAttribute('name');

            switch ($field->nodeName) {
                case 'select':
                    $option = $crawler->filter('[name^="' . $attr . '"] option:last-child');
                    $value = $option->getNode(0)->getAttribute('value');
                    break;
                case 'textarea':
                    $value = $field->nodeValue;
                    break;
                default:
                    $value = $field->getAttribute('value');
            }

            $newValue = $value;

            if ($writeData !== null) {
                if (empty($value)) {
                    /** Для полей где нужен url, имя поля должно содержать текст "Url" */
                    if (strpos($attr,'Url') !== false) {
                        $newValue = sprintf('https://%s.address.com', $writeData);
                    } else {
                        $newValue = $writeData;
                    }
                }
            }

            $dataForm[$attr] = $newValue;
        }

        return $dataForm;
    }
}
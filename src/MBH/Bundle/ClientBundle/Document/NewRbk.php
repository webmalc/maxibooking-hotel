<?php
/**
 * Created by PhpStorm.
 * Date: 06.06.18
 */

namespace MBH\Bundle\ClientBundle\Document;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NewRbk
 * @package MBH\Bundle\ClientBundle\Document\PaymentSystem
 * @ODM\EmbeddedDocument()
 */
class NewRbk implements PaymentSystemInterface
{
    const NAME_TYPE_API_KEY = 'newRbkApiKey';

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $apiKey;

    /**
     * @param FormInterface $form
     * @return NewRbk
     */
    public static function instance(FormInterface $form): self
    {
        $entity = new self();
        $entity->setApiKey($form->get(self::NAME_TYPE_API_KEY)->getData());

        return $entity;
    }

    /**
     * @return string
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        // TODO: Implement getFormData() method.
    }

    public function checkRequest(Request $request)
    {
        // TODO: Implement checkRequest() method.
    }

    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        // TODO: Implement getSignature() method.
    }
}
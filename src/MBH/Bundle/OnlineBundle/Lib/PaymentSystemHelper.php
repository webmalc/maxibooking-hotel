<?php
/**
 * Created by PhpStorm.
 * Date: 31.01.19
 */

namespace MBH\Bundle\OnlineBundle\Lib;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\ExtraData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PaymentSystemHelper
{
    /**
     * @var TranslatorInterface
     */
    private $trans;

    /**
     * @var ClientConfig
     */
    private $clientConfig;

    /**
     * @var ExtraData
     */
    private $extra;

    /**
     * @var SearchForm
     */
    private $searchForm;

    /**
     * @var bool
     */
    private $isOnePaymentSystem = false;

    /**
     * @var string
     */
    private $optionsForHtmlTagSelect = '';

    /**
     * @var array
     */
    private $usedPaymentSystems;

    /**
     * SearchFormExtraHtmlData constructor.
     */
    public function __construct(ContainerInterface $container, ClientConfig $clientConfig, SearchForm $search)
    {
        $this->trans = $container->get('translator');
        $this->clientConfig = $clientConfig;
        $this->searchForm = $search;
        $this->usedPaymentSystems = $this->clientConfig->getPaymentSystems();
        $this->extra = $container->get('mbh.payment_extra_data');
        $this->isOnePaymentSystem = count($this->usedPaymentSystems) <= 2;

        $this->generateOptionsTagAsString();
    }

    /**
     * @return SearchForm
     */
    public function getSearchForm(): SearchForm
    {
        return $this->searchForm;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTrans(): TranslatorInterface
    {
        return $this->trans;
    }

    /**
     * @return array
     */
    public function getUsedPaymentSystems(): array
    {
        return $this->usedPaymentSystems;
    }

    /**
     * @return string
     */
    public function getHtmlOptionsForSelectTag(): string
    {
        return $this->optionsForHtmlTagSelect;
    }

    /**
     * @return bool
     */
    public function isOnePaymentSystem(): bool
    {
        return $this->isOnePaymentSystem;
    }

    private function generateOptionsTagAsString(): void
    {
        $paymentSystems = array_map(
            function ($systemName) {
                return $this->trans->trans($systemName);
            },
            $this->extra->getPaymentSystems()
        );

        $format = '<option value="%s"' . ($this->isOnePaymentSystem ? ' selected' : '') . '>%s</option>';

        $html = '';
        foreach ($this->usedPaymentSystems as $paymentSystem) {
            $html .= sprintf($format, $paymentSystem, $paymentSystems[$paymentSystem]);
        }

        $this->optionsForHtmlTagSelect = $html;
    }
}
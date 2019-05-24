<?php
/**
 * Created by PhpStorm.
 * Date: 31.01.19
 */

namespace MBH\Bundle\OnlineBundle\Services\PaymentForm;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\ExtraData;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
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
     * @var ContactHotelPaymentForm
     */
    private $contactHotel;

    /**
     * SearchFormExtraHtmlData constructor.
     */
    public function __construct(
        TranslatorInterface $translator,
        ClientConfigManager $clientConfigManager,
        ExtraData $extraData,
        ContactHotelPaymentForm $contactHotel
    )
    {
        $this->trans = $translator;
        $this->clientConfig = $clientConfigManager->fetchConfig();
        $this->extra = $extraData;
        $this->contactHotel = $contactHotel;

        $this->usedPaymentSystems = $this->clientConfig->getPaymentSystems();
        $this->isOnePaymentSystem = count($this->usedPaymentSystems) <= 2;

        $this->generateOptionsTagAsString();
    }

    /**
     * @param SearchForm $searchForm
     */
    public function setSearchForm(SearchForm $searchForm): self
    {
        $this->searchForm = $searchForm;
        $this->contactHotel->setSearchForm($searchForm);

        return $this;
    }

    public function getTextForWarning(): string
    {
        if ($this->getUsedPaymentSystems() === []) {
            return $this->contactHotel->getText();
        }

        return '';
    }

    /**
     * @return SearchForm
     */
    public function getSearchForm(): SearchForm
    {
        return $this->searchForm;
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

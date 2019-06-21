<?php
/**
 * Date: 21.06.19
 */

namespace MBH\Bundle\BaseBundle\Service;


use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;

class CurrencySymbol
{
    /**
     * @var ClientConfig
     */
    private $clientConfig;

    /**
     * @var array
     */
    private $currencyDataList;

    /**
     * @var array
     */
    private $currencyData;

    /**
     * @var string
     */
    private $template;

    public function __construct(ClientConfigManager $clientConfigManager, array $currencyDataList)
    {
        $this->clientConfig = $clientConfigManager->fetchConfig();
        $this->currencyDataList = $currencyDataList;

        $this->currencyData = $currencyDataList[$this->clientConfig->getCurrency()];

        $this->generateTemplate();
    }

    public function symbolWithPrice(string $price, ?string $wrapperId = null, ?string $wrapperTag = null): string
    {
        if ($wrapperId !== null) {
            $wrapperId = sprintf('id="%s" ', $wrapperId);
        }

        if ($wrapperTag === null) {
            $wrapperTag = 'span';
        }

        $formatPrice = sprintf('<%1$s %2$sclass="price-wrapper">%3$s</%1$s>', $wrapperTag, $wrapperId, $price);

        return sprintf($this->template, $formatPrice);
    }

    private function generateTemplate(): void
    {
        if ($this->currencyData['side'] === 'left') {
            $first = $this->stringWithSymbol(true);
            $second = '%s';
        } else {
            $first = '%s';
            $second = $this->stringWithSymbol(false);
        }

        $this->template = sprintf('%s%s', $first, $second);
    }

    private function stringWithSymbol(bool $isFirst = true): string
    {
        $symbol = $this->currencyData['symbol'] ?? $this->currencyData['text'];
        $cssClass = $isFirst ? 'currency-symbol-first' : 'currency-symbol-second';

        return sprintf('<span class="currency-symbol %s">%s</span>', $cssClass, $symbol);
    }
}

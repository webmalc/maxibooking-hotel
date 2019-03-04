<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\DataProviders\SpecialDataProvider;

class OnlineSearchHelper
{

    /** @var array */
    private $options;

    /** @var  \SplObjectStorage */
    private $dataProviders;
    /** @var  Helper */
    private $helper;

    /** @var OnlineDataProviderWrapperInterface */
    private $additionalProvider;

    /**
     * OnlineSearchHelper constructor.
     * @param array $options
     * @param Helper $helper
     */
    public function __construct(array $options, Helper $helper)
    {
        $this->options = $options;
        $this->dataProviders = new \SplObjectStorage();
        $this->helper = $helper;
    }

    public function addDataProvider(OnlineDataProviderWrapperInterface $dataProvider)
    {
        $this->dataProviders->attach($dataProvider);
    }

    public function setAdditionalProvider(OnlineDataProviderWrapperInterface $dataProvider)
    {
        $this->additionalProvider = $dataProvider;
    }

    /**
     * @param OnlineSearchFormData $formInstance
     * @return array
     * TODO: Очень костыльно получилось с доп датами. В идеале рефакторить и тут. Лимит получения лучше бы в интерфейс ?.
     */
    public function getResults(OnlineSearchFormData $formInstance)
    {
        $results = [];
        if (!$this->isAdditionalData($formInstance)) {
            foreach ($this->dataProviders as $dataProvider) {
                /** @var OnlineDataProviderWrapperInterface $dataProvider */
                //** 04032019 из за нового поиска добавлен костыль внутри getResults чтоб на спец поиск не был запущен новый */
                $dataResults = $dataProvider->getResults($formInstance);
                if ($dataProvider->getType() === SpecialDataProvider::TYPE) {
                    //** Костылище для ограничения лимита по выводу спец */
                    $limit = $this->options['show_special_restrict'];
                    if ($limit) {
                        $dataResults = \array_slice($dataResults, 0, $limit, true);
                        //** Костылище для сортировки по просьбе Малеевой от 21.06.2018 */
                        usort(
                            $dataResults,
                            function ($resA, $resB) {
                                /** @var OnlineResultInstance $resA */
                                /** @var OnlineResultInstance $resB */
                                $priceA = $resA->getResults()->first()->getPrices();
                                $priceB = $resB->getResults()->first()->getPrices();

                                return reset($priceA) <=> reset($priceB);
                            }
                        );
                    }
                }
                $results[$dataProvider->getType()] = $dataResults;
            }
            if (\count($results)) {
                $results = $this->finishFilter($results);
            }
        } else {
            /** When Additional dates */
            $results = $this->additionalProvider->getResults($formInstance);
        }

        return $results;
    }

    private function isAdditionalData(OnlineSearchFormData $formData): bool
    {
        return $this->options['add_search_dates'] && $formData->isAddDates();
    }

    private function finishFilter(
        array $searchResults
    ) {
        $result = [];
        $isCommon = isset($searchResults['common']) && !empty($searchResults['common']);
        $isSpecials = isset($searchResults['special']) && !empty($searchResults['special']);
        if ($isCommon && $isSpecials) {
            $this->injectQueryIdInSpecial(reset($searchResults['common'])->getQueryId(), $searchResults['special']);
            //** Еще один грязный костыль */
            foreach (range(1, 3) as $index) {
                $middleResult = array_shift($searchResults['special']);
                if ($middleResult) {
                    $result[] = $middleResult;
                } else {
                    break;
                }
            }

            $result = array_merge($result, $searchResults['common'], $searchResults['special']);

            return $result;
        }
        foreach ($searchResults as $searchResult) {
            $result = array_merge($result, $searchResult);

        }

        return $result;
    }

    private function injectQueryIdInSpecial(?string $queryId = null, array &$specials)
    {
        if ($queryId) {
            foreach ($specials as $special) {
                /** @var OnlineResultInstance $special */
                $special->setQueryId($queryId);
            }
        }

    }
}

<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConfig;
use MBH\Bundle\SearchBundle\Form\SearchConditionsType;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class SearchConditionsCreator
{
    /** @var FormFactory */
    private $formFactory;
    /**
     * @var SearchConfig
     */
    private $config;


    /**
     * SearchRequestReceiver constructor.
     * @param FormFactory $factory
     * @param SearchConfig $config
     */
    public function __construct(FormFactory $factory, SearchConfig $config)
    {
        $this->formFactory = $factory;
        $this->config = $config;
    }

    /**
     * @param array $data
     * @return SearchConditions
     * @throws SearchConditionException
     */
    public function createSearchConditions(array $data): SearchConditions
    {
        try {
            $options = [];
            /** Для прогрева кэша нужно включить эту опцию, в случае если используются категорию иначе не правильно отработает форма. */
            if ($data['isForceDisableCategory'] ?? null) {
                $options['isForceDisableCategory'] = true;
                $options['allow_extra_fields'] = true;
            }
            $conditionForm = $this->formFactory->create(SearchConditionsType::class, null, $options);
            $conditionForm->submit($data);
        } catch (AlreadySubmittedException|InvalidOptionsException $e) {
            throw new SearchConditionException('Error when try to submit form to create SearchConditions');
        }

        if (!$conditionForm->isValid()) {
            throw new SearchConditionException('No valid SearchConditions data.'.$conditionForm->getErrors(true, false));
        }
        /** @var SearchConditions $searchConditions */
        $searchConditions = $conditionForm->getData();
        if (!$searchConditions->getAdditionalResultsLimit()) {
            $searchConditions->setAdditionalResultsLimit($this->config->getRoomTypeResultsShowAmount());
        }
        /** @var SearchConditions $searchConditions */
        $hash = uniqid(\AppKernel::DEFAULT_CLIENT, true);
        $searchConditions->setSearchHash($hash);

        return $searchConditions;
    }


}
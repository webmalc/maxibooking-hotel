<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
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
     * SearchRequestReceiver constructor.
     * @param FormFactory $factory
     */
    public function __construct(FormFactory $factory)
    {
        $this->formFactory = $factory;
    }

    /**
     * @param array $data
     * @return SearchConditions
     * @throws SearchConditionException
     */
    public function createSearchConditions(array $data): SearchConditions
    {
        try {
            $conditionForm = $this->formFactory->create(SearchConditionsType::class);
            $conditionForm->submit($data);
        } catch (AlreadySubmittedException|InvalidOptionsException $e) {
            throw new SearchConditionException('Error when try to submit form to create SearchConditions');
        }

        if (!$conditionForm->isValid()) {
            throw new SearchConditionException('No valid SearchConditions data.'.$conditionForm->getErrors());
        }

        return $conditionForm->getData();
    }


}
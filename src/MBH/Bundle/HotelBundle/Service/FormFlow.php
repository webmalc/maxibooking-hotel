<?php

namespace MBH\Bundle\HotelBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Service\HotelSelector;
use MBH\Bundle\HotelBundle\Document\FlowConfig;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class FormFlow
{
    /** @var DocumentManager  */
    protected $dm;
    /** @var HotelSelector */
    protected $hotelSelector;
    /** @var FlowConfig */
    protected $flowConfig;
    /** @var FormFactory */
    protected $formFactory;
    /** @var Request */
    protected $request;

    public function setDm(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function setHotelSelector(HotelSelector $hotelSelector)
    {
        $this->hotelSelector = $hotelSelector;
    }

    public function setFormFactory(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function setRequestStack(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
        if ($this->request === null) {
            throw new \RuntimeException('The request is not available.');
        }
    }

    abstract protected function getStepsConfig();

    /**
     * @param $data
     * @return FormInterface
     */
    public function createForm($data = null)
    {
        $formType = $this->getCurrentStepInfo()['form_type'];
        $definedOptions = isset($this->getCurrentStepInfo()['options']) ? $this->getCurrentStepInfo()['options'] : [];

        return $this->formFactory->create($formType, $data, array_merge($definedOptions, [
            'flow_step' => $this->getCurrentStepNumber(),
            'hasGroups' => false
        ]));
    }

    /**
     * @param Base|null $document
     * @return FormFlow
     */
    public function init(Base $document = null)
    {
        $this->flowConfig = $this->getFlowConfig($this->getFlowId($document));

        return $this;
    }

    /**
     * @return bool
     */
    public function nextStep()
    {
        if ($this->isButtonClicked('back')) {
            if ($this->isFirstStep()) {
                throw new \RuntimeException('So this is the first step!');
            }
            $this->flowConfig->decreaseStepNumber();
        } else {
            if ($this->isLastStep()) {
                throw new \RuntimeException('There are no steps after current!');
            }

            $this->flowConfig->increaseStepNumber();
        }

        $this->dm->flush($this->flowConfig);

        return true;
    }

    /**
     * @return bool
     */
    public function isLastStep()
    {
        return $this->getNumberOfSteps() <= $this->getCurrentStepNumber();
    }

    /**
     * @return bool
     */
    public function isFirstStep()
    {
        return $this->getCurrentStepNumber() === 1;
    }

    public function getCurrentStepNumber()
    {
        return $this->flowConfig->getCurrentStepNumber();
    }

    public function getNumberOfSteps()
    {
        return count($this->getStepsConfig());
    }

    /**
     * @return bool
     */
    public function isBackButtonClicked()
    {
        return $this->isButtonClicked('back');
    }

    /**
     * @return bool
     */
    public function isNextButtonClicked()
    {
        return $this->isButtonClicked('next');
    }

    public function isButtonClicked(string $buttonName)
    {
        return $this->request->request->has($buttonName);
    }

    public function reset()
    {
        $this->flowConfig->setCurrentStep(1);
    }


    protected function getDocumentForForm($step)
    {
        
    }

    /**
     * @return array
     */
    public function getStepLabels()
    {
        return array_map(function (array $stepConfig) {
            return $stepConfig['label'];
        }, $this->getStepsConfig());
    }

    /**
     * @return string
     */
    public function getCurrentStepLabel()
    {
        return $this->getCurrentStepInfo()['label'];
    }

    /**
     * @param string $flowId
     * @return FlowConfig|null
     */
    protected function getFlowConfig(string $flowId)
    {
        $config = $this->dm
            ->getRepository('MBHHotelBundle:FlowConfig')
            ->findOneBy(['flowId' => $flowId, 'isEnabled' => true]);

        if (is_null($config)) {
            $config = (new FlowConfig())
                ->setFlowId($flowId);
            $this->dm->persist($config);
        }

        return $config;
    }

    /**
     * @return array
     */
    protected function getCurrentStepInfo()
    {
        return $this->getStepsConfig()[$this->getCurrentStepNumber() - 1];
    }

    /**
     * @param Base $document
     * @return string
     */
    private function getFlowId(Base $document): string
    {
        $documentId = !is_null($document) && !empty($document->getId())
            ? $document->getId()
            : $this->hotelSelector->getSelected()->getId();
        $flowId = static::class . $documentId;

        return $flowId;
    }
}
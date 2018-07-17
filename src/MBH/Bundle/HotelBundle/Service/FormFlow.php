<?php

namespace MBH\Bundle\HotelBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Service\HotelSelector;
use MBH\Bundle\HotelBundle\Document\FlowConfig;
use Symfony\Component\Form\FormInterface;

abstract class FormFlow
{
    /** @var DocumentManager  */
    protected $dm;
    /** @var Base */
    protected $hotelSelector;
    /** @var FlowConfig */
    protected $flowConfig;

    public function __construct(DocumentManager $dm, HotelSelector $hotelSelector) {
        $this->dm = $dm;
        $this->hotelSelector = $hotelSelector;
    }

    abstract protected function getStepsConfig();

    /**
     * @return FormInterface
     */
    public function createForm()
    {
        $formType = $this->getStepsConfig()[$this->getCurrentStepNumber()]['form_type'];

    }

    /**
     * @param Base|null $document
     */
    public function init(Base $document = null)
    {
        $this->flowConfig = $this->getFlowConfig($this->getFlowId($document));
    }

    /**
     * @return bool
     */
    public function nextStep()
    {
        if ($this->isLastStep()) {
            throw new \RuntimeException('There are no steps after current!');
        }

        $this->flowConfig->increaseStepNumber();

        return true;
    }

    /**
     * @return bool
     */
    public function isLastStep()
    {
        return $this->getNumberOfSteps() >= $this->getCurrentStepNumber();
    }

    public function getCurrentStepNumber()
    {
        return $this->flowConfig->getCurrentStepNumber();
    }

    public function getNumberOfSteps()
    {
        return count($this->getStepsConfig());
    }

    protected function getDocumentForForm($step)
    {
        
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
        }

        return $config;
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
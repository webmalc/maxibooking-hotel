<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document()
 * Class FlowItem
 * @package MBH\Bundle\HotelBundle\Document
 */
class FlowConfig extends Base
{
    /**
     * @ODM\Field(type="int")
     * @var int
     */
    private $currentStep = 1;

    /**
     * Id of the base document, handled in first step of the flow
     * @var string
     * @ODM\Field(type="string")
     */
    private $flowId;

    /**
     * @return int
     */
    public function getCurrentStepNumber(): ?int
    {
        return $this->currentStep;
    }

    /**
     * @return FlowConfig
     */
    public function increaseStepNumber(): FlowConfig
    {
        $this->setCurrentStep($this->getCurrentStepNumber() + 1);

        return $this;
    }

    /**
     * @return FlowConfig
     */
    public function decreaseStepNumber(): FlowConfig
    {
        $this->setCurrentStep($this->getCurrentStepNumber() - 1);

        return $this;
    }

    /**
     * @param int $currentStep
     * @return FlowConfig
     */
    public function setCurrentStep(int $currentStep): FlowConfig
    {
        $this->currentStep = $currentStep;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlowId(): ?string
    {
        return $this->flowId;
    }

    /**
     * @param string $flowId
     * @return FlowConfig
     */
    public function setFlowId(string $flowId): FlowConfig
    {
        $this->flowId = $flowId;

        return $this;
    }
}
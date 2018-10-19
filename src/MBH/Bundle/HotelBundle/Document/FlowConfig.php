<?php

namespace MBH\Bundle\HotelBundle\Document;

use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;

/**
 * @ODM\Document()
 * Class FlowItem
 * @package MBH\Bundle\HotelBundle\Document
 */
class FlowConfig extends Base
{
    use TimestampableDocument;
    use BlameableDocument;

    /**
     * @ODM\Field(type="int")
     * @var int
     */
    private $currentStep = 1;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $flowId;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $flowData;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $flowType;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    private $isFinished = false;

    /**
     * @return bool
     */
    public function isFinished(): ?bool
    {
        return $this->isFinished;
    }

    /**
     * @param bool $isFinished
     * @return FlowConfig
     */
    public function setIsFinished(bool $isFinished): FlowConfig
    {
        $this->isFinished = $isFinished;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlowType(): ?string
    {
        return $this->flowType;
    }

    /**
     * @param string $flowType
     * @return FlowConfig
     */
    public function setFlowType(string $flowType): FlowConfig
    {
        $this->flowType = $flowType;

        return $this;
    }

    /**
     * @return array
     */
    public function getFlowData(): array
    {
        return $this->flowData ? json_decode($this->flowData, true) : [];
    }

    /**
     * @param array $flowData
     * @return FlowConfig
     */
    public function setFlowData(array $flowData): FlowConfig
    {
        $this->flowData = json_encode($flowData);

        return $this;
    }

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
    public function setFlowId(?string $flowId): FlowConfig
    {
        $this->flowId = $flowId;

        return $this;
    }
}
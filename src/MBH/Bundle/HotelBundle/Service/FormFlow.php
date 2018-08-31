<?php

namespace MBH\Bundle\HotelBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\FlowConfig;
use MBH\Bundle\HotelBundle\Model\FlowRuntimeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class FormFlow
{
    /** @var DocumentManager */
    protected $dm;
    /** @var FlowConfig */
    protected $flowConfig;
    /** @var FormFactory */
    protected $formFactory;
    /** @var Request */
    protected $request;
    protected $customErrors = [];


    private $isFlowConfigInit = false;

    public function setDm(DocumentManager $dm)
    {
        $this->dm = $dm;
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

    abstract protected function getStepsConfig(): array;

    abstract protected function getFormData();

    /**
     * @param FormInterface $form
     * @throws FlowRuntimeException
     */
    abstract protected function handleForm(FormInterface $form);

    public function handleStepAndGetForm()
    {
        $form = $this->createForm();
        $form->handleRequest($this->request);
        $flowExceptions = [];

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleForm($form);
            } catch (FlowRuntimeException $exception) {
                $flowExceptions[] = $exception->getMessage();
            }

            if (empty($flowExceptions)) {
                if ($this->isNextButtonClicked() || $this->isBackButtonClicked()) {
                    $this->nextStep();
                }

                if ($this->isFinishButtonClicked()) {
                    $this->getFlowConfig()->setIsFinished(true);
                    $this->isFlowConfigInit = false;
                }
            }

            $this->dm->flush();
            $form = $this->createForm();

            foreach ($flowExceptions as $exceptionMessage) {
                $form->addError(new FormError($exceptionMessage));
            }
        }

        return $form;
    }

    /**
     * @param $data
     * @param array $options
     * @return FormInterface
     */
    public function createForm($options = [])
    {
        $data = $this->getFormData();
        if (!isset($this->getCurrentStepInfo()['form_type'])) {
            throw new \InvalidArgumentException(
                'There is no "form_type" parameter in step config #'.$this->getCurrentStepNumber()
            );
        }

        $formType = $this->getCurrentStepInfo()['form_type'];
        $definedOptions = isset($this->getCurrentStepInfo()['options']) ? $this->getCurrentStepInfo()['options'] : [];

        return $this->formFactory->create(
            $formType,
            $data,
            array_merge(
                $definedOptions,
                $options,
                [
                    'flow_step' => $this->getCurrentStepNumber(),
                    'hasGroups' => false,
                ]
            )
        );
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
            $this->getFlowConfig()->decreaseStepNumber();
        } else {
            if ($this->isLastStep()) {
                throw new \RuntimeException('There are no steps after current!');
            }

            $this->getFlowConfig()->increaseStepNumber();
        }

        $this->dm->flush($this->getFlowConfig());

        return true;
    }

    /**
     * @return bool
     */
    public function isLastStep()
    {
        return $this->getNumberOfSteps() === $this->getCurrentStepNumber();
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
        return $this->getFlowConfig()->getCurrentStepNumber();
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
        $this->getFlowConfig()->setCurrentStep(1);
    }


    protected function getDocumentForForm($step)
    {

    }

    /**
     * @return array
     */
    public function getStepLabels()
    {
        return array_map(
            function (array $stepConfig) {
                return $stepConfig['label'];
            },
            $this->getStepsConfig()
        );
    }

    /**
     * @return string
     */
    public function getCurrentStepLabel()
    {
        return $this->getCurrentStepInfo()['label'];
    }

    public function isStepSkipped()
    {
        return false;
    }

    public function isStepDone()
    {
        return true;
    }

    /**
     * @return FlowConfig
     */
    public function getFlowConfig()
    {
        if (!$this->isFlowConfigInit) {
            $flowId = $this->getFlowId();
            $config = $this->dm
                ->getRepository('MBHHotelBundle:FlowConfig')
                ->findOneBy(['flowId' => $flowId, 'isEnabled' => true, 'isFinished' => false]);

            if (is_null($config)) {
                $config = (new FlowConfig())
                    ->setFlowId($flowId);
                $this->dm->persist($config);
            }

            $this->flowConfig = $config;
            $this->isFlowConfigInit = true;
        }

        return $this->flowConfig;
    }

    /**
     * @return bool
     */
    protected function isFinishButtonClicked()
    {
        return $this->isLastStep() && $this->isButtonClicked('finish');
    }

    /**
     * @return array
     */
    protected function getCurrentStepInfo()
    {
        return $this->getStepsConfig()[$this->getCurrentStepNumber() - 1];
    }

    /**
     * @return array
     */
    protected function getFlowData()
    {
        return $this->getFlowConfig()->getFlowData();
    }

    /**
     * @return string
     */
    private function getFlowId(): string
    {
        return static::class;
    }
}
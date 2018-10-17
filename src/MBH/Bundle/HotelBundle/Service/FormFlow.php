<?php

namespace MBH\Bundle\HotelBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\FlowConfig;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Model\FlowRuntimeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

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
    /** @var TranslatorInterface */
    protected $translator;
    /** @var Session */
    protected $session;

    protected $flowId;

    private $isFlowInitiated = false;
    private $isFlowConfigInit = false;

    abstract public static function getFlowType();

    abstract protected function getStepsConfig(): array;

    abstract protected function getFormData();

    /**
     * @param FormInterface $form
     * @throws FlowRuntimeException
     */
    abstract protected function handleForm(FormInterface $form);

    /**
     * @param Hotel $hotel
     * @param string|null $flowId
     * @return self
     */
    public function init(string $flowId = null)
    {
        $this->flowId = $flowId;
        $this->isFlowInitiated = true;

        return $this;
    }

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

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

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
                if ($this->mustChangeStep()) {
                    $this->changeStep();
                }

                if ($this->isFinishButtonClicked()) {
                    $this->onFinishButtonClick();
                }
            }

            if (!$this->isPreparatoryStep()) {
                $this->dm->persist($this->getFlowConfig());
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
     * @param array $options
     * @return FormInterface
     */
    public function createForm($options = [])
    {
        $data = $this->getFormData();
        if (!isset($this->getCurrentStepInfo()['form_type'])) {
            throw new \InvalidArgumentException(
                'There is no "form_type" parameter in step config with id "' . $this->getStepId() . '"'
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
                    'flow_step' => $this->getStepId(),
                    'hasGroups' => false,
                ]
            )
        );
    }

    /**
     * @return bool
     */
    public function changeStep()
    {
        if ($this->isButtonClicked('back')) {
            if ($this->isFirstStep()) {
                throw new \RuntimeException('There are no steps before current!');
            }
            $this->getFlowConfig()->decreaseStepNumber();
        } else {
            if ($this->isLastStep()) {
                throw new \RuntimeException('There are no steps after current!');
            }

            $this->getFlowConfig()->increaseStepNumber();
        }

        $this->dm->flush();

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

    public function getStepId(int $stepNumber = null)
    {
        $stepInfo = is_null($stepNumber) ? $this->getCurrentStepInfo() : $this->getStepsConfig()[$stepNumber];

        return $stepInfo['id'] ?? $this->getCurrentStepNumber();
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

    /**
     * @return FlowConfig
     */
    public function getFlowConfig()
    {
        if (!$this->isFlowConfigInit) {
            $flowId = $this->getFlowId();
            $config = is_null($flowId) ? null : $this->findFlowConfig($flowId);

            if (is_null($config)) {
                $config = (new FlowConfig())->setFlowType(static::getFlowType());
            }

            $this->flowConfig = $config;
            $this->isFlowConfigInit = true;
        }

        return $this->flowConfig;
    }


    /**
     * @return bool
     */
    public function isFlowStarted()
    {
        return !empty($this->getFlowConfig()->getId());
    }

    /**
     * @return float|int
     */
    public function getProgressRate()
    {
        if (!$this->isFlowStarted()) {
            return 0;
        }

        return round(($this->getCurrentStepNumber() - 1) / $this->getNumberOfSteps(), 2) * 100;
    }

    /**
     * @param $flowId
     * @return FlowConfig|null|object
     */
    public function findFlowConfig($flowId)
    {
        return $this->dm
            ->getRepository('MBHHotelBundle:FlowConfig')
            ->findOneBy([
                'flowId' => $flowId,
                'isEnabled' => true,
                'flowType' => static::getFlowType()
            ]);
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
    public function getFlowId(): ?string
    {
        if (!$this->isFlowInitiated) {
            throw new \RuntimeException('FormFlow is not initiated!');
        }

        return $this->flowId;
    }

    public function getTemplateParameters()
    {
        return [];
    }

    public function isPreparatoryStep()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function mustChangeStep(): bool
    {
        return $this->isNextButtonClicked() || $this->isBackButtonClicked();
    }

    protected function onFinishButtonClick(): void
    {
        $this->reset();
        $this->addSuccessFinishFlash();
    }

    protected function addFlash(string $message, string $type = 'success')
    {
        $this->session->getFlashBag()->add($type, $message);
    }

    protected function addSuccessFinishFlash() {
        $this->addFlash('Настройка успешно завершена!');
    }
}
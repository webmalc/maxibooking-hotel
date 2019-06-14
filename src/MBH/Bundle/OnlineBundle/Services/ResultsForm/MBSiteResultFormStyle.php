<?php
/**
 * Date: 28.05.19
 */

namespace MBH\Bundle\OnlineBundle\Services\ResultsForm;


use MBH\Bundle\OnlineBundle\Interfaces\MBSite\StyleDataInterface;

class MBSiteResultFormStyle
{
    private const FORM_NAME = 'results-form';

    private const MAIN_FILE = 'main-style.css';
    private const STEP_ONE_BUTTON_FILE = 'step-one-button.css';

    /**
     * @var StyleDataInterface
     */
    private $styleData;

    public function __construct(StyleDataInterface $styleData)
    {
        $this->styleData = $styleData;
    }

    public function getMainStyle(): ?string
    {
        return $this->styleData->getStyleData(self::MAIN_FILE, self::FORM_NAME);
    }

    public function getStepOneButtonStyle(): ?string
    {
        return $this->styleData->getStyleData(self::STEP_ONE_BUTTON_FILE, self::FORM_NAME);
    }
}

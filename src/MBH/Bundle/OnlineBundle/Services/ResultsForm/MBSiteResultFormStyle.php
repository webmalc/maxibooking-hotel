<?php
/**
 * Date: 28.05.19
 */

namespace MBH\Bundle\OnlineBundle\Services\ResultsForm;


use MBH\Bundle\OnlineBundle\Lib\MBSite\StyleDataInterface;

class MBSiteResultFormStyle
{
    private const FORM_NAME = 'results-form';

    private const FILE_NAME = 'main-style.css';

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
        return $this->styleData->getContent(self::FILE_NAME, self::FORM_NAME);
    }
}

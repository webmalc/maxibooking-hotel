<?php
/**
 * Date: 07.05.19
 */

namespace MBH\Bundle\OnlineBundle\Services\SearchForm;


use MBH\Bundle\OnlineBundle\Lib\MBSite\StyleDataInterface;

class MBSiteSearchFormStyle
{
    private const FORM_NAME = 'search-form';

    private const FILE_STYLE_SEARCH_IFRAME = 'search-form.css';
    private const FILE_STYLE_CALENDAR_IFRAME = 'calendar.css';
    private const FILE_STYLE_ADDITIONAL_IFRAME = 'additional-form.css';

    /**
     * @var StyleDataInterface
     */
    private $styleData;

    public function __construct(StyleDataInterface $styleData)
    {
        $this->styleData = $styleData;
    }

    public function getStyleSearchForm(): ?string
    {
        return $this->getContent(self::FILE_STYLE_SEARCH_IFRAME);
    }

    public function getStyleCalendar(): ?string
    {
        return $this->getContent(self::FILE_STYLE_CALENDAR_IFRAME);
    }

    public function getStyleAdditionalForm(): ?string
    {
        return $this->getContent(self::FILE_STYLE_ADDITIONAL_IFRAME);
    }

    private function getContent(string $file): ?string
    {
        return $this->styleData->getContent($file, self::FORM_NAME);
    }
}

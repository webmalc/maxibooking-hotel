<?php


namespace Tests\Bundle\SearchBundle;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

abstract class SearchWebTestCase extends WebTestCase
{
    /**
     * @param array $documents
     * @param string $documentFullTitle
     * @return mixed
     */
    protected function getDocumentFromArrayByFullTitle(array $documents, string $documentFullTitle)
    {
        $filter = function ($document) use ($documentFullTitle) {
            return $document->getFullTitle() === $documentFullTitle;
        };
        $documentFiltered = array_filter($documents, $filter);

        return reset($documentFiltered);
    }
}
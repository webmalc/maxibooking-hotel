<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchResultHolder
 * @package MBH\Bundle\SearchBundle\Document
 * @ODM\Document(collection="SearchResultHolders", repositoryClass="SearchResultHolderRepository")
 */
class SearchResultHolder extends Base
{
    /**
     * @var array
     * @Assert\NotNull()
     * @ODM\Field(type="collection")
     */
    private $takenSearchResultIds = [];

    /**
     * @var string
     * @Assert\NotNull()
     * @ODM\Field(type="string")
     */
    private $searchConditionsId;

    /**
     * @return array
     */
    public function getTakenSearchResultIds(): array
    {
        return $this->takenSearchResultIds;
    }

    /**
     * @param array $takenSearchResultIds
     * @return SearchResultHolder
     */
    public function setTakenSearchResultIds(array $takenSearchResultIds): SearchResultHolder
    {
        $this->takenSearchResultIds = $takenSearchResultIds;

        return $this;
    }

    public function addTakenResultIds(array $takenSearchResultIds): SearchResultHolder
    {
        array_merge($this->takenSearchResultIds, $takenSearchResultIds);

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchConditionsId()
    {
        return $this->searchConditionsId;
    }

    /**
     * @param string $searchConditionsId
     * @return SearchResultHolder
     */
    public function setSearchConditionsId(string $searchConditionsId): SearchResultHolder
    {
        $this->searchConditionsId = $searchConditionsId;

        return $this;
    }






//    public function getAsyncResults(): ?array
//    {
//        if ($this->takenSearchResults->count() === $this->expectedResultsCount) {
//            return null;
//        }
//
//        $results = array_diff($this->getSearchResults()->toArray(), $this->getTakenSearchResults()->toArray());
//        if (\count($results)) {
//            foreach ($results as $result) {
//                $this->addTakenSearchResults($result);
//            }
//        }
//
//        return $results;
//    }


}